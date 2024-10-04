<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\HasEvents;

abstract class Sign
{
    use HasEvents;

    public function __construct(
        public readonly Road $road,
    )
    {
    }

    /**
     * Create the station
     *
     * @param Update $update
     * @return Station
     */
    public abstract function createStation(Update $update): Station;

    /**
     * Get event dynamic arguments for the sign
     *
     * @param string $event
     * @return array
     */
    public function getEventDynamicArgs(string $event): array
    {
        return [
            'road' => $this->road,
            'sign' => $this,
            ...$this->getEventDefaultDynamicArgs($event),
        ];
    }


    /**
     * Listen event before creating station
     *
     * @param Closure $callback
     * @return $this
     */
    public function creatingStation(Closure $callback)
    {
        return $this->listen('creatingStation', $callback);
    }

    /**
     * Listen event after creating station
     *
     * @param Closure $callback
     * @return $this
     */
    public function createdStation(Closure $callback)
    {
        return $this->listen('createdStation', $callback);
    }

}