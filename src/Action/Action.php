<?php

namespace Mmb\Action;

use BadMethodCallException;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\Attributes\InlineAttribute;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineCreateRegister;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Inline\Register\InlineReloadRegister;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Menu;
use Mmb\Auth\AreaRegister;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Update;
use Mmb\Support\AttributeLoader\HasAttributeLoader;
use Mmb\Support\Auth\AuthorizeClass;
use Mmb\Support\Caller\AuthorizationHandleBackException;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\StatusHandleBackException;
use Mmb\Support\Db\ModelFinder;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Alias;

abstract class Action
{
    use HasAttributeLoader;
    use AuthorizesRequests
    {
        authorizeResource as private;
    }

    public Update $update;

    public function __construct(
        Update $update = null,
    )
    {
        $this->update = $update ?? app(Update::class);
        $this->boot();
    }

    /**
     * Boot action
     *
     * @return void
     */
    protected function boot()
    {
    }


    private array $_valueCaches = [];

    /**
     * Make cache
     *
     * @param string  $name
     * @param Closure $value
     * @return mixed
     */
    protected function cache(string $name, Closure $value)
    {
        return array_key_exists($name, $this->_valueCaches) ?
            $this->_valueCaches[$name] :
            $this->_valueCaches[$name] = $value();
    }

    /**
     * Make cache of finding model by property
     *
     * @template T
     * @param class-string<T> $model
     * @param string          $name
     * @param string          $findBy
     * @return T
     */
    protected function modelOf(string $model, string $name, string $findBy = '')
    {
        return $this->cache(
            $name . ':model',
            function() use ($model, $name, $findBy)
            {
                if($value = @$this->$name)
                {
                    if($value instanceof $model)
                    {
                        return $value;
                    }

                    return ModelFinder::findByOrFail($model, $findBy, $value);
                }

                abort(404);
            },
        );
    }

    /**
     * Invoke a method
     *
     * @param string $method
     * @param        ...$args
     * @return mixed
     */
    public function invoke(string $method, ...$args)
    {
        try
        {
            app(AreaRegister::class)->authorize(static::class);

            return Caller::invoke([$this, $method], $args, $this->getInvokeDynamicParameters($method));
        }
        catch(AuthorizationException $exception)
        {
            if(!($exception instanceof AuthorizationHandleBackException))
            {
                if(
                    method_exists($this, $fn = 'errorAuthorize' . $exception->status()) ||
                    method_exists($this, $fn = 'errorAuthorize') ||
                    method_exists($this, $fn = 'error403')
                )
                {
                    throw AuthorizationHandleBackException::from($exception, [$this, $fn]);
                }
            }

            throw $exception;
        }
        catch(HttpException $exception)
        {
            if(!($exception instanceof StatusHandleBackException))
            {
                if(
                    method_exists($this, $fn = 'error' . $exception->getStatusCode()) ||
                    method_exists($this, $fn = 'error')
                )
                {
                    throw StatusHandleBackException::from($exception, [$this, $fn]);
                }
            }

            throw $exception;
        }
    }

    /**
     * Invoke a method with dynamic parameters
     *
     * @param string $method
     * @param array  $normalArgs
     * @param array  $dynamicArgs
     * @return mixed
     */
    public function invokeDynamic(string $method, array $normalArgs, array $dynamicArgs)
    {
        try
        {
            app(AreaRegister::class)->authorize(static::class);

            return Caller::invoke(
                [$this, $method], $normalArgs, $dynamicArgs + $this->getInvokeDynamicParameters($method)
            );
        }
        catch(AuthorizationException $exception)
        {
            if(!($exception instanceof AuthorizationHandleBackException))
            {
                if(
                    method_exists($this, $fn = 'errorAuthorize' . $exception->status()) ||
                    method_exists($this, $fn = 'errorAuthorize') ||
                    method_exists($this, $fn = 'error403')
                )
                {
                    throw AuthorizationHandleBackException::from($exception, [$this, $fn]);
                }
            }

            throw $exception;
        }
        catch(HttpException $exception)
        {
            if(!($exception instanceof StatusHandleBackException))
            {
                if(
                    method_exists($this, $fn = 'error' . $exception->getStatusCode()) ||
                    method_exists($this, $fn = 'error')
                )
                {
                    throw StatusHandleBackException::from($exception, [$this, $fn]);
                }
            }

            throw $exception;
        }
    }

    /**
     * Create an instance and invoke the method
     *
     * @param string $method
     * @param        ...$args
     * @return mixed
     */
    public static function invokes(string $method, ...$args)
    {
        return (method_exists(static::class, 'make') ? static::make() : new static)->invoke($method, ...$args);
    }

    /**
     * Create an instance and invoke the method
     *
     * @param string $method
     * @param array  $normalArgs
     * @param array  $dynamicArgs
     * @return mixed
     */
    public static function invokeDynamics(string $method, array $normalArgs = [], array $dynamicArgs = [])
    {
        return (method_exists(static::class, 'make') ? static::make() : new static)->invokeDynamic($method, $normalArgs, $dynamicArgs);
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
        if ($alias = $this->getInlineAliases()[$register->method] ?? false)
        {
            return $this->$alias(...);
        }

        if (!method_exists($this, $register->method))
        {
            if ($register->inlineAction instanceof Dialog)
            {
                if (method_exists($this, $register->method . 'dialog'))
                {
                    return $this->{$register->method . 'dialog'}(...);
                }
            }
            elseif ($register->inlineAction instanceof Menu)
            {
                if (method_exists($this, $register->method . 'menu'))
                {
                    return $this->{$register->method . 'menu'}(...);
                }
            }
            elseif ($register->inlineAction instanceof InlineForm)
            {
                if (method_exists($this, $register->method . 'form'))
                {
                    return $this->{$register->method . 'form'}(...);
                }
            }

            throw new BadMethodCallException(sprintf("Call to undefined inline method [%s] on [%s]", $register->method, static::class));
        }

        return $this->{$register->method}(...);
    }

    protected function onInitializeInlineRegister(InlineRegister $register)
    {
    }

    public function createInlineRegister(string|InlineAction $inlineAction, string $name, array $args)
    {
        $register = new InlineCreateRegister(
            $this->update,
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
            $this->update,
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
        $newInlineAction = new (get_class($inlineAction))($this->update);

        $register = new InlineReloadRegister(
            $this->update,
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
        if (!app(AreaRegister::class)->can(static::class))
        {
            return false;
        }

        foreach (static::getClassAttributesOf(AuthorizeClass::class) as $auth)
        {
            if(!$auth->can())
            {
                return false;
            }
        }

        if (isset($method))
        {
            try
            {
                foreach (static::getMethodAttributesOf($method, AuthorizeClass::class) as $auth)
                {
                    if (!$auth->can())
                    {
                        return false;
                    }
                }
            }
            catch (\Throwable $e) { }
        }

        return true;
    }

    /**
     * Get bot
     *
     * @return Bot
     */
    public function bot()
    {
        return $this->update?->bot() ?? app(Bot::class);
    }

    /**
     * Response to update message
     *
     * @param       $message
     * @param array $args
     * @param mixed ...$namedArgs
     * @return mixed
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

}
