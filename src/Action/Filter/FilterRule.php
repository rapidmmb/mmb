<?php

namespace Mmb\Action\Filter;

use Mmb\Core\Updates\Update;

abstract class FilterRule
{

    /**
     * Pass update and check filter
     *
     * @param Update $update
     * @param        $value
     * @return void
     */
    public function pass(Update $update, &$value)
    {
    }

    /**
     * Set error message and return false
     *
     * @param $message
     * @throws FilterFailException
     */
    public function fail($message)
    {
        throw new FilterFailException($message, "Filter [".class_basename(static::class)."] failed");
    }

}
