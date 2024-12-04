<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Context;
use Mmb\Core\Updates\Update;

class BeInt extends BeText
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
            if (!is_numeric($text) || str_contains($text, '.'))
            {
                $this->fail(value($this->numberError ?? __('mmb::filter.unsigned-int')));
            }
            $value = (int) +$text;

            if ($value < 0)
            {
                $this->fail(value($this->numberError ?? __('mmb::filter.unsigned-int')));
            }
        }
        else
        {
            if (!is_numeric($text) || str_contains($text, '.'))
            {
                $this->fail(value($this->numberError ?? __('mmb::filter.int')));
            }
            $value = (int) +$text;
        }
    }

}
