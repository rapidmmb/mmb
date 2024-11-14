<?php

namespace Mmb\Action\Inline\Attributes;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_METHOD)]
class UseEvents
{

    public array $events;

    public function __construct(
        array|string ...$events,
    )
    {
        $this->events = array_map(fn ($event) => strtolower($event), Arr::flatten($events));
    }

}