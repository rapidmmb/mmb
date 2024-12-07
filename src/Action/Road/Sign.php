<?php

namespace Mmb\Action\Road;

use Mmb\Core\Updates\Update;

abstract class Sign extends WeakSign
{

    /**
     * Create the station
     *
     * @param string $name
     * @return Station
     */
    public abstract function createStation(string $name) : Station;

}
