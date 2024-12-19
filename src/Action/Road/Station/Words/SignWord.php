<?php

namespace Mmb\Action\Road\Station\Words;

use Mmb\Action\Road\Sign;
use Mmb\Support\Caller\HasEvents;

/**
 * @template T of Sign
 */
abstract class SignWord
{
    use HasEvents;

    public function __construct(
        /**
         * @var T $sign
         */
        protected Sign $sign,
    )
    {
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

}