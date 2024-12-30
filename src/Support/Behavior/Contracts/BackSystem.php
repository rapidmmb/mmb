<?php

namespace Mmb\Support\Behavior\Contracts;

use Mmb\Context;

interface BackSystem
{

    public function back(Context $context, array $args, array $dynamicArgs) : void;

}