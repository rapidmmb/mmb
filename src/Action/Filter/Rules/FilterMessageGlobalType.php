<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Context;
use Mmb\Core\Updates\Update;

class FilterMessageGlobalType extends BeMessage
{

    public array $types;

    public function __construct(
        string|array $types,
        public $typeError,
        $messageError = null
    )
    {
        parent::__construct($messageError);

        $this->types = array_map('strtolower', (array) $types);
    }

    public function pass(Context $context, Update $update, &$value)
    {
        parent::pass($context, $update, $value);

        if (!in_array(strtolower($update->message->globalType), $this->types))
        {
            $this->fail(value($this->typeError));
        }
    }

}