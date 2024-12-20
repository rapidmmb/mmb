<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\WeakSign;
use Mmb\Support\Caller\HasEvents;

/**
 * @template T of Sign
 */
abstract class SignWord extends WeakSign
{
    use HasEvents;

    public function __construct(
        /**
         * @var T $sign
         */
        protected Sign $sign,
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

    /**
     * Call a callback
     *
     * @param Closure|string|array $event
     * @param ...$args
     * @return mixed
     */
    public function call(Closure|string|array $event, ...$args)
    {
        return $this->sign->getStation()->fireSignAs($this, $event, ...$args);
    }

}