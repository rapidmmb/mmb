<?php

namespace Mmb\Action\Road;

use Illuminate\Support\Arr;
use Mmb\Action\Road\Attributes\StationParameterResolverAttributeContract;
use Mmb\Context;
use Mmb\Support\Caller\HasEvents;
use Closure;

abstract class WeakSign
{
    use HasEvents;

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
        foreach (class_uses_recursive($this) as $trait) {
            if (method_exists($this, $method = 'boot' . class_basename($trait))) {
                $this->$method();
            }
        }
    }

    protected function shutdown()
    {
        foreach (class_uses_recursive($this) as $trait) {
            if (method_exists($this, $method = 'shutdown' . class_basename($trait))) {
                $this->$method();
            }
        }
    }

    public function die()
    {
        $this->shutdown();
    }

    /**
     * Get the root sign
     *
     * @return Sign
     */
    abstract public function getRoot(): Sign;

    /**
     * Call a callback with station scope
     *
     * @param Closure|string|array $event
     * @param ...$args
     * @return mixed
     */
    public function call(Closure|string|array $event, ...$args)
    {
        return $this->getRoot()->callAs($this, $event, ...$args);
    }


    /**
     * Get event dynamic arguments for the sign
     *
     * @param string $event
     * @return array
     */
    protected function getEventDynamicArgs(string $event): array
    {
        return [
            'road' => $this->road,
            'sign' => $this,
            ...$this->getEventDefaultDynamicArgs($event),
        ];
    }


    protected array $params = [];

    /**
     * Add parameters when opening the station
     *
     * @param string|array|Closure|null $params
     * @param string|array|null $names
     * @return $this
     */
    public function params(null|string|array|Closure $params, null|string|array $names = null)
    {
        if (is_string($params) || is_array($params)) {
            $names = $params;
            $params = null;
        }

        if (is_null($params) && is_null($names)) {
            return $this;
        }

        $resolvers = [];

        if ($params) {
            $autoNames = is_null($names);
            $names ??= [];

            $ref = new \ReflectionFunction($params);
            foreach ($ref->getParameters() as $parameter) {
                if ($autoNames || in_array($parameter->getName(), $names)) {
                    if ($attribute = Arr::first(
                        $parameter->getAttributes(),
                        fn(\ReflectionAttribute $attribute) => is_a(
                            $attribute->getName(), StationParameterResolverAttributeContract::class, true,
                        ),
                    )) {
                        $resolvers[$parameter->getName()] = [$attribute->newInstance(), $parameter];

                        if ($autoNames) {
                            $names[] = $parameter->getName();
                        }
                    }
                }
            }
        } else {
            $names = (array)$names;
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