<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Context;
use Mmb\Core\Updates\Update;

class BeNumber extends BeText
{

    public function __construct(
        public      $numberError = null,
                    $textError = null,
                    $messageError = null,
        public bool $unsigned = false,
    )
    {
        parent::__construct($textError, $messageError);
    }

    public function pass(Context $context, Update $update, &$value)
    {
        parent::pass($context, $update, $value);

        $text = $update->message->text;
        if ($this->unsigned)
        {
            if (!is_numeric($text))
            {
                $this->fail(value($this->numberError ?? __('mmb::filter.unsigned-number')));
            }
            $value = +$text;

            if ($value < 0)
            {
                $this->fail(value($this->numberError ?? __('mmb::filter.unsigned-number')));
            }
        }
        else
        {
            if (!is_numeric($text))
            {
                $this->fail(value($this->numberError ?? __('mmb::filter.number')));
            }
            $value = +$text;
        }
    }

}