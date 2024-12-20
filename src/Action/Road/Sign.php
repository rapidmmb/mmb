<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Core\Updates\Update;

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
    public abstract function getStation(): Station;

    /**
     * Reset the station
     *
     * @return void
     */
    public abstract function resetStation(): void;

    /**
     * Call a callback
     *
     * @param Closure|string|array $event
     * @param ...$args
     * @return mixed
     */
    public function call(Closure|string|array $event, ...$args)
    {
        return $this->getStation()->fireSignAs($this, $event, ...$args);
    }

}
