<?php

namespace Mmb\Action\Road\Station;

use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;

class Voter
{

    public function __construct(
        public Station $station,
        public array $args = [],
        public array $specific = [],
    )
    {
    }

    public function for(string $name): array
    {
        return array_merge($this->args, $this->specific[$name] ?? []);
    }

    public function call(object $instance, string $event, $callable = null)
    {
        return $instance->fire($callable ?? $event, ...$this->for($event));
    }

}