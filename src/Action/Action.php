<?php

namespace Mmb\Action;

use BadMethodCallException;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Mmb\Action\Inline\Attributes\UseEvents;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineCreateRegister;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Inline\Register\InlineReloadRegister;
use Mmb\Auth\AreaRegister;
use Mmb\Context;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Exceptions\AbortException;
use Mmb\Support\AttributeLoader\AttributeLoader;
use Mmb\Support\AttributeLoader\HasAttributeLoader;
use Mmb\Support\Auth\AuthorizeClass;
use Mmb\Support\Caller\AuthorizationHandleBackException;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\StatusHandleBackException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @property-read HigherOrderSafeProxy<static>|static $safe
 * @property-read HigherOrderSafeProxy<static>|static $unsafe
 */
abstract class Action
{
    use HasAttributeLoader;
    use AuthorizesRequests {
        authorizeResource as private;
    }

    public readonly Update $update;

    public function __construct(
        public Context $context,
    )
    {
        $this->update = $this->context->update;
        $this->boot();
    }

    public static function makeByContext(Context $context): static
    {
        return new static($context);
    }

    /**
     * Boot action
     *
     * @return void
     */
    protected function boot()
    {
    }

    /**
     * Get denied handler method name
     *
     * @param \Throwable $e
     * @return string|null
     */
    public function getDeniedHandler(\Throwable $e): ?string
    {
        if ($e instanceof AuthorizationException) {
            if (
                method_exists($this, $fn = 'deniedAuthorize' . $e->status()) ||
                method_exists($this, $fn = 'deniedAuthorize') ||
                method_exists($this, $fn = 'denied403')
            ) {
                return $fn;
            }
        } elseif ($e instanceof AbortException) {
            if (
                method_exists($this, $fn = 'denied' . $e->errorType) ||
                method_exists($this, $fn = 'denied')
            ) {
                return $fn;
            }
        } elseif ($e instanceof HttpException) {
            if (
                method_exists($this, $fn = 'denied' . $e->getStatusCode()) ||
                method_exists($this, $fn = 'denied')
            ) {
                return $fn;
            }
        }

        return null;
    }


    /**
     * Invoke a method
     *
     * @param string $_method
     * @param        ...$_args
     * @return mixed
     */
    public function invoke(string $_method, ...$_args)
    {
        return (new HigherOrderSafeProxy($this, true, true, $this->getInvokeDynamicParameters($_method)))
            ->__call($_method, $_args);
    }

    /**
     * Invoke a method with dynamic parameters
     *
     * @param string $method
     * @param array $normalArgs
     * @param array $dynamicArgs
     * @return mixed
     */
    public function invokeDynamic(string $method, array $normalArgs, array $dynamicArgs)
    {
        return (new HigherOrderSafeProxy($this, true, true, $dynamicArgs + $this->getInvokeDynamicParameters($method)))
            ->__call($method, $normalArgs);
    }

    /**
     * Create an instance and invoke the method
     *
     * @param string $_method
     * @param        ...$_args
     * @return mixed
     */
    public static function invokes(Context $_context, string $_method, ...$_args)
    {
        return static::makeByContext($_context)->invoke($_method, ...$_args);
    }

    /**
     * Create an instance and invoke the method
     *
     * @param string $method
     * @param array $normalArgs
     * @param array $dynamicArgs
     * @return mixed
     */
    public static function invokeDynamics(Context $context, string $method, array $normalArgs = [], array $dynamicArgs = [])
    {
        return static::makeByContext($context)->invokeDynamic($method, $normalArgs, $dynamicArgs);
    }


    protected $inlineAliases = [];

    /**
     * Get inline aliasees
     *
     * @return array|mixed
     */
    protected function getInlineAliases()
    {
        return $this->inlineAliases;
    }

    /**
     * Get inline alias for inline action
     *
     * @param InlineRegister $register
     * @return Closure|null
     */
    protected function getInlineCallbackFor(InlineRegister $register)
    {
        if ($alias = $this->getInlineAliases()[$register->method] ?? false) {
            return $this->$alias(...);
        }

        if (!method_exists($this, $register->method)) {
            // if ($register->inlineAction instanceof Dialog)
            // {
            //     if (method_exists($this, $register->method . 'dialog'))
            //     {
            //         return $this->{$register->method . 'dialog'}(...);
            //     }
            // }
            // elseif ($register->inlineAction instanceof Menu)
            // {
            //     if (method_exists($this, $register->method . 'menu'))
            //     {
            //         return $this->{$register->method . 'menu'}(...);
            //     }
            // }
            // elseif ($register->inlineAction instanceof InlineForm)
            // {
            //     if (method_exists($this, $register->method . 'form'))
            //     {
            //         return $this->{$register->method . 'form'}(...);
            //     }
            // }

            throw new BadMethodCallException(sprintf("Call to undefined inline method [%s] on [%s]", $register->method, static::class));
        }

        $method = $register->method;
        return $this->$method(...);
    }

    public static function getInlineUsingEvents(string $name): array
    {
        if (method_exists(static::class, $name)) {
            return AttributeLoader::getMethodAttributeOf(static::class, $name, UseEvents::class)?->events ?? [];
        }

        return [];
    }

    protected function onInitializeInlineRegister(InlineRegister $register)
    {
    }

    public function createInlineRegister(string|InlineAction $inlineAction, string $name, array $args)
    {
        $register = new InlineCreateRegister(
            $this->context,
            $inlineAction,
            target: $this,
            method: $name,
            callArgs: $args,
        );

        $register->init = $this->getInlineCallbackFor($register);

        $this->onInitializeInlineRegister($register);

        return $register;
    }

    public function loadInlineRegister(InlineAction $inlineAction, string $name)
    {
        $register = new InlineLoadRegister(
            $this->context,
            $inlineAction,
            target: $this,
            method: $name,
        );

        $register->init = $this->getInlineCallbackFor($register);

        $this->onInitializeInlineRegister($register);

        return $register;
    }

    public function reloadInlineRegister(InlineAction $inlineAction)
    {
        $newInlineAction = new (get_class($inlineAction))($this->context);

        $register = new InlineReloadRegister(
            $this->context,
            $newInlineAction,
            target: $this,
            method: $inlineAction->getInitializer()[1],
        );

        $register->init = $this->getInlineCallbackFor($register);

        $register->from($inlineAction);
        $this->onInitializeInlineRegister($register);

        return $register;
    }

    /**
     * Get dynamic parameters when invoke a method
     *
     * @param string $method
     * @return array
     */
    protected function getInvokeDynamicParameters(string $method)
    {
        return [];
    }

    /**
     * Checks the class or special method is allowed
     *
     * @param string|null $method
     * @return bool
     */
    public static function allowed(?string $method = null)
    {
        if (!app(AreaRegister::class)->can(static::class)) {
            return false;
        }

        foreach (static::getClassAttributesOf(AuthorizeClass::class) as $auth) {
            if (!$auth->can()) {
                return false;
            }
        }

        if (isset($method)) {
            try {
                foreach (static::getMethodAttributesOf($method, AuthorizeClass::class) as $auth) {
                    if (!$auth->can()) {
                        return false;
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return true;
    }

    /**
     * Get the current bot
     *
     * @return ?Bot
     */
    public function bot(): ?Bot
    {
        return $this->context->bot;
    }

    /**
     * Get the current update
     *
     * @deprecated
     * @return Update|null
     */
    public function update(): ?Update
    {
        return $this->context->update;
    }

    /**
     * Get safe proxy or call a callback via safe proxy
     *
     * @param string|Closure|null $callback
     * @param ...$args
     * @return HigherOrderSafeProxy<static>|static
     */
    public function safe(string|Closure|null $callback = null, ...$args)
    {
        $safeProxy = new HigherOrderSafeProxy($this);

        if (is_null($callback)) {
            return $safeProxy;
        }

        return is_string($callback) ?
            $safeProxy->$callback(...$args) :
            $safeProxy->callSafety($callback);
    }

    /**
     * Response to update message
     *
     * @param       $message
     * @param array $args
     * @param mixed ...$namedArgs
     * @return Message|null
     */
    public function response($message, array $args = [], ...$namedArgs)
    {
        return $this->update->response($message, $args, ...$namedArgs);
    }

    /**
     * Tell message to update callback / other
     *
     * @param       $message
     * @param array $args
     * @param mixed ...$namedArgs
     * @return ?bool
     */
    public function tell($message = null, array $args = [], ...$namedArgs)
    {
        return $this->update->tell($message, $args, ...$namedArgs);
    }

    public function __get(string $name)
    {
        if ($name == 'safe') {
            return new HigherOrderSafeProxy($this);
        }

        if ($name == 'unsafe') {
            return new HigherOrderSafeProxy($this, false);
        }

        throw new \Exception(sprintf("Try to access undefined property [%s] on [%s]", $name, static::class));
    }

}
