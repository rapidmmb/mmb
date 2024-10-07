<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Caller\HasEvents;

abstract class Sign
{
    use HasEvents,
        Station\Concerns\DefineStubs;

    public function __construct(
        public readonly Road $road,
    )
    {
        $this->boot();
    }

    /**
     * Create the station
     *
     * @param string $name
     * @param Update $update
     * @return Station
     */
    public abstract function createStation(string $name, Update $update) : Station;

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


    protected array $params = [];

    /**
     * Add parameters when opening the station
     *
     * @param string|array $params
     * @param Closure|null $callback
     * @return $this
     */
    public function params(string|array $params, ?Closure $callback = null)
    {
        $this->params[] = [(array) $params, $callback];
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