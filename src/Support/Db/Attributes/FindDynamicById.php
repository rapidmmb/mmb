<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindDynamicById extends FindDynamicBy
{

    public function __construct(
        ?int  $error = null,
        mixed $failMessage = null,
        bool  $nullOnFail = false,
        bool  $withTrashed = false,
    )
    {
        parent::__construct(null, $error, $failMessage, $nullOnFail, $withTrashed);
    }

}
