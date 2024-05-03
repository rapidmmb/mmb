<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Core\Updates\Update;

class FilterRegex extends BeText
{

    public function __construct(
        public string $pattern,
        public int $result = -1, // Pass -2 to get all
        public $error = null,
    )
    {
    }

    public function pass(Update $update, &$value)
    {
        if($value instanceof Update)
        {
            parent::pass($update, $value);
        }

        if(!is_string($value) && !is_int($value) && !is_float($value))
        {
            $this->fail(__('mmb.filter.text'));
        }

        if(!preg_match($this->pattern, $value, $matches))
        {
            $this->fail(value($this->error ?? __('mmb.filter.pattern')));
        }

        if($this->result >= 0)
        {
            $value = $matches[$this->result];
        }
        elseif($this->result == -2)
        {
            $value = $matches;
        }
    }

}
