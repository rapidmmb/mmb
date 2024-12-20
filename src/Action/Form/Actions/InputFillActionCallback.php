<?php

namespace Mmb\Action\Form\Actions;

use Mmb\Context;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\Action\ReservedCallbackTypes;

class InputFillActionCallback extends ActionCallback
{

    public function __construct(
        protected $value,
    )
    {
        parent::__construct(null);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isNamed(): bool
    {
        return false;
    }

    public function isStorable(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [ReservedCallbackTypes::InputFill, $this->action];
    }

    public static function fromArray(array $array): ?static
    {
        if (count($array) != 2 || $array[0] != ReservedCallbackTypes::InputFill) {
            return null;
        }

        return new static($array[1]);
    }

    public function invoke($object, Context $context, array $args, array $dynamicArgs)
    {
        if ($pass = $dynamicArgs['pass'] ?? null) {

            $pass($this->getValue());

        } elseif ($sender = $dynamicArgs['sender'] ?? null) {

            $sender->value = $this->getValue();

        }
    }

}