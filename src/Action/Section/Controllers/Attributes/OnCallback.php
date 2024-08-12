<?php

namespace Mmb\Action\Section\Controllers\Attributes;

use Attribute;
use Mmb\Action\Action;
use Mmb\Action\Section\Controllers\QueryMatcher;

#[Attribute(Attribute::TARGET_METHOD)]
class OnCallback extends BaseAttribute
{

    public function getExtraAction(Action $action, string $method)
    {
        return null;
    }

}
