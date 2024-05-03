<?php

namespace Mmb\Action;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\Attributes\InlineAttribute;
use Mmb\Action\Inline\InlineAction;
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
     * @param string       $name
     * @param InlineAction $inline
     * @return array|string|null
     */
    protected function getInlineAliasFor(string $name, InlineAction $inline)
    {
        if($alias = $this->getInlineAliases()[$name] ?? false)
        {
            return $alias;
        }

        if(!method_exists($this, $name))
        {
            if($inline instanceof Menu)
            {
                if(method_exists($this, $name . 'menu'))
                {
                    return $name . 'menu';
                }
            }
            elseif($inline instanceof InlineForm)
            {
                if(method_exists($this, $name . 'form'))
                {
                    return $name . 'form';
                }
            }
        }

        return null;
    }

    /**
     * Load inline action from
     *
     * @template T of InlineAction
     * @param string              $name
     * @param class-string<T>|InlineAction $inline
     * @param array               $args
     * @return T
     */
    public function initializeInline(string $name, string|InlineAction $inline, array $args = [])
    {
        if(is_string($inline))
            $inline = new $inline($this->update);
        $this->initializeInlineObject($name, $inline);

        $alias = $this->getInlineAliasFor($name, $inline) ?? $name;

        if(is_string($alias))
        {
            $class = static::class;
            $object = $this;
            $name = $alias;
            $attrs = static::getMethodAttributesOf($name, InlineAttribute::class);
        }
        elseif(is_array($alias) && count($alias) == 2)
        {
            $class = $alias[0];
            if(!is_a($class, Action::class, true))
                throw new \TypeError(sprintf("Invalid alias class type, required [%s], given [%s]", Alias::class, is_string($class) ? $class : smartTypeOf($class)));
            $object = is_string($class) ? (method_exists($class, 'make') ? $class::make() : new $class()) : $class;
            $name = $alias[1];
            $attrs = $class::getMethodAttributesOf($name, InlineAttribute::class);
        }
        else
        {
            throw new \InvalidArgumentException(sprintf("Invalid alias type of [%s], should be string or array<2>", smartTypeOf($alias)));
        }

        foreach($attrs as $attr)
        {
            $attr->before($inline);
        }

        if($inline->isCreating())
        {
            $args = $class::getNormalizedCallingMethod(
                $name, $args,
                fn(\ReflectionParameter $parameter, $value) => $inline->have(
                    $parameter->getName(),
                    $_,
                    $value
                ),
            );
        }
        elseif($inline->isLoading())
        {
            $parameters = (new \ReflectionMethod($object, $name))->getParameters();
            unset($parameters[0]);
            foreach($parameters as $parameter)
            {
                $inline->have($parameter->getName(), $argument);
                $args[$parameter->getName()] = $argument;
            }
        }

        foreach($attrs as $attr)
        {
            $attr->modifyArgs($inline, $args);
        }

        $object->invokeDynamic(
            $name, [$inline, ...$args], []
        );

        foreach($attrs as $attr)
        {
            $attr->after($inline);
        }

        return $inline;
    }

    protected function initializeInlineObject(string $name, InlineAction $inline)
    {
        $inline->initializer($this, $name);
    }

    /**
     * Make menu
     *
     * @param string      $name
     * @param InlineAction        $inline
     * @param Update|null $update
     * @return InlineAction
     */
    public static function initializeInlineOf(string $name, InlineAction $inline, Update $update = null)
    {
        $instance = new static($update);
        return $instance->initializeInline($name, $inline);
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
        if(!app(AreaRegister::class)->can(static::class))
        {
            return false;
        }

        foreach(static::getClassAttributesOf(AuthorizeClass::class) as $auth)
        {
            if(!$auth->can())
            {
                return false;
            }
        }

        if(isset($method))
        {
            try
            {
                foreach(static::getMethodAttributesOf($method, AuthorizeClass::class) as $auth)
                {
                    if(!$auth->can())
                    {
                        return false;
                    }
                }
            }
            catch(\Exception $e) { }
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
     * @return void
     */
    public function tell($message, array $args = [], ...$namedArgs)
    {
        return $this->update->tell($message, $args, ...$namedArgs);
    }

}
