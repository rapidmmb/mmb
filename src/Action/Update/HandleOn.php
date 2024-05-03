<?php

namespace Mmb\Action\Update;

use Closure;

class HandleOn extends HandleCondition
{

    public function __construct(
        public $condition,
               $action
    )
    {
        parent::__construct($action);
    }

    public function check()
    {
        return (bool) value($this->condition);
    }

}
