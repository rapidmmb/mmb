<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindDynamicById extends FindDynamicBy
{

    public function __construct(
        ?int $error = 404,
    )
    {
        parent::__construct('', $error);
    }

}
