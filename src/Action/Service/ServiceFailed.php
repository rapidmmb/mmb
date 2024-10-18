<?php

namespace Mmb\Action\Service;

use Mmb\Core\Updates\Update;
use Mmb\Support\Exceptions\CallableException;

class ServiceFailed extends \Exception implements CallableException
{

    public function __construct(
        public int  $failCode = 0, public mixed $failMessage = null, string $message = "", int $code = 0,
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function invoke(Update $update)
    {
        if ($this->failMessage)
        {
            $update->response($this->failMessage);
        }
    }

}