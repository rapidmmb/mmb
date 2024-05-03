<?php

namespace Mmb\Action\Section\Controllers\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
abstract class BaseAttribute
{

    public function __construct(
        public ?string $pattern = null,
        public bool $full = false,
        public ?string $name = null,
    )
    {
    }

}
