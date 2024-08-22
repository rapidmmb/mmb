<?php

namespace Mmb\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AbortException extends HttpException
{

    public function __construct(
        public int|string $errorType,
        public mixed      $errorMessage = null,
        ?\Throwable       $previous = null
    )
    {
        parent::__construct(
            is_int($errorType) ? $errorType : 400,
            is_string($errorMessage) ? $errorMessage : '',
            $previous
        );
    }

}