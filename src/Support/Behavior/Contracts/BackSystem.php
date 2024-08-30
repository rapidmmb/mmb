<?php

namespace Mmb\Support\Behavior\Contracts;

interface BackSystem
{

    public function back(array $args, array $dynamicArgs) : void;

}