<?php

namespace Mmb\Action\Road;

use Illuminate\Support\Arr;
use Mmb\Action\Road\Attributes\StationParameterResolverAttributeContract;
use Mmb\Context;
use Mmb\Support\Caller\HasEvents;
use Closure;

abstract class WeakSign
{
    use HasEvents,
        Station\Concerns\DefineStubs;

    public Context $context;

    public function __construct(
        public readonly Road $road,
    )
    {
        $this->context = $road->context;
        $this->boot();
    }

    protected function boot()
    {
        foreach (class_uses_recursive($this) as $trait)
        {
            if (method_exists($this, $method = 'boot' . class_basename($trait)))
            {
                $this->$method();
            }
        }
    }

    protected function shutdown()
    {
        foreach (class_uses_recursive($this) as $trait)
        {
            if (method_exists($this, $method = 'shutdown' . class_basename($trait)))
            {
                $this->$method();
            }
        }
    }

    public function die()
    {
        $this->shutdown();
    }


    private array $definedMethods = [];

    private array $definedEventOptions = [];

    protected final function defineMethod(string $name, Closure $callback)
    {
        $this->definedMethods[$name] = $callback;
    }

    protected final function defineEvent(string $name, array $options)
    {
        $this->definedEventOptions[$name] = $options;
    }

    /**
     * Get event dynamic arguments for the sign
     *
     * @param string $event
     * @return array
     */
    protected function getEventDynamicArgs(string $event) : array
    {
        return [
            'road' => $this->road,
            'sign' => $this,
            ...$this->getEventDefaultDynamicArgs($event),
        ];
    }

    protected function getEventOptions(string $event) : array
    {
        return array_key_exists($event, $this->definedEventOptions) ?
            $this->definedEventOptions[$event] :
            $this->getEventDefaultOptions($event);
    }

    public function __call(string $name, array $arguments)
    {
        if (array_key_exists($name, $this->definedMethods))
        {
            return ($this->definedMethods[$name])(...$arguments);
        }

        throw new \BadMethodCallException(sprintf("Call to undefined method [%s] on [%s]", $name, static::class));
    }

    /**
     * Fire an event using station
     *
     * @param Station              $station
     * @param string|array|Closure $event
     * @param                      ...$args
     * @return mixed
     */
    protected function fireBy(Station $station, string|array|Closure $event, ...$args)
    {
        return $station->fireSignAs($this, $event, ...$args);
    }


    protected array $params = [];

    /**
     * Add parameters when opening the station
     *
     * @param string|array|Closure|null $params
     * @param string|array|null         $names
     * @return $this
     */
    public function params(null|string|array|Closure $params, null|string|array $names = null)
    {
        if (is_string($params) || is_array($params))
        {
            $names = $params;
            $params = null;
        }

        if (is_null($params) && is_null($names))
        {
            return $this;
        }

        $resolvers = [];

        if ($params)
        {
            $autoNames = is_null($names);
            $names ??= [];

            $ref = new \ReflectionFunction($params);
            foreach ($ref->getParameters() as $parameter)
            {
                if ($autoNames || in_array($parameter->getName(), $names))
                {
                    if ($attribute = Arr::first(
                        $parameter->getAttributes(),
                        fn (\ReflectionAttribute $attribute) => is_a(
                            $attribute->getName(), StationParameterResolverAttributeContract::class, true
                        )
                    ))
                    {
                        $resolvers[$parameter->getName()] = [$attribute->newInstance(), $parameter];

                        if ($autoNames)
                        {
                            $names[] = $parameter->getName();
                        }
                    }
                }
            }
        }
        else
        {
            $names = (array) $names;
        }

        $this->params[] = [$names, $params, $resolvers];
        return $this;
    }

    /**
     * Get the params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

}