<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Core\Updates\Update;

class BeText extends BeMessage
{

    public function __construct(
        public $textError = null,
               $messageError = null
    )
    {
        parent::__construct($messageError);
    }

    public function pass(Update $update, &$value)
    {
        parent::pass($update, $value);

        if ($update->message->type != 'text')
        {
            $this->fail(value($this->textError ?? __('mmb::filter.text')));
        }

        $value = $update->message->text;
    }

}
