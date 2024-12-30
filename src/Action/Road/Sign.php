<?php

namespace Mmb\Action\Road;

use Closure;

/**
 * @template T of Station
 */
abstract class Sign extends WeakSign
{

    public function __construct(
        Road                   $road,
        public readonly string $name,
    )
    {
        parent::__construct($road);
    }

    /**
     * Get the related station
     *
     * @return T
     */
    abstract public function createStation(): Station;

    /**
     * Get the root sign
     *
     * @return Sign
     */
    public function getRoot(): Sign
    {
        return $this;
    }


    /**
     * Reset the station
     *
     * @return void
     */
    abstract public function resetStation(): void;

    /**
     * Call a callback
     *
     * @param Closure|string|array $event
     * @param ...$args
     * @return mixed
     */
    public function call(Closure|string|array $event, ...$args)
    {
        return $this->callAs($this, $event, ...$args);
    }

    /**
     * Call a callback in other sign
     *
     * @param WeakSign $sign
     * @param Closure|string|array $event
     * @param ...$args
     * @return mixed
     */
    public function callAs(WeakSign $sign, Closure|string|array $event, ...$args)
    {
        return $sign->fire($event, ...$args);
    }

}
