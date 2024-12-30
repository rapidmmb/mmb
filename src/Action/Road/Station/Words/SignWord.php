<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\WeakSign;
use Mmb\Support\Caller\HasEvents;

/**
 * @template T of WeakSign
 */
abstract class SignWord extends WeakSign
{
    use HasEvents;

    public function __construct(
        /**
         * @var T $sign
         */
        protected WeakSign $sign,
    )
    {
        parent::__construct($sign->road);
    }

    public static function make(Sign $sign): static
    {
        return new static($sign);
    }

    /**
     * @param ...$parameters
     * @return T
     */
    public function assign(...$parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->$key($value);
        }

        return $this->sign;
    }

    public function getRoot(): Sign
    {
        return $this->sign->getRoot();
    }

    public function __clone(): void
    {
        foreach (get_object_vars($this) as $key => $value) {
            if (isset($value) && $value instanceof SignWord) {
                $this->$key = clone $value;
            }
        }
    }

}