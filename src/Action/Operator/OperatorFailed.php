<?php

namespace Mmb\Action\Operator;

use Mmb\Context;
use Mmb\Support\Exceptions\CallableException;

class OperatorFailed extends \Exception implements CallableException
{

    public function __construct(
        public mixed $tag,
        public ?string $failMessage,
    )
    {
        parent::__construct("Operator failed");
    }

    public function invoke(Context $context)
    {
        $context->update->response($this->failMessage);
    }

}