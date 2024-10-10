<?php

namespace Mmb\Action\Road;

use Mmb\Core\Updates\Update;

abstract class Sign extends WeakSign
{

    /**
     * Create the station
     *
     * @param string $name
     * @param Update $update
     * @return Station
     */
    public abstract function createStation(string $name, Update $update) : Station;

}
