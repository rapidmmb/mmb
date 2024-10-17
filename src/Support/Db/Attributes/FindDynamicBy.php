<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindDynamicBy extends FindDynamic
{

    public function __construct(
        string $key,
        ?int   $error = null,
        mixed  $failMessage = null,
        bool   $nullOnFail = false,
        bool   $withTrashed = false,
    )
    {
        parent::__construct($key ?: null, $error, $failMessage, $nullOnFail, $withTrashed);
    }

}
