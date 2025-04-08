<?php

namespace Mmb\Exceptions;

class TrackingException extends \Exception
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
    }
}