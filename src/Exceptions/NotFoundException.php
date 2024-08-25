<?php

namespace Mmb\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{

    public function __construct(
        public int|string $errorType,
        public mixed      $errorMessage = null,
        ?\Throwable       $previous = null
    )
    {
        parent::__construct(
            is_string($errorMessage) ? $errorMessage : '',
            $previous
        );
    } // TODO

}