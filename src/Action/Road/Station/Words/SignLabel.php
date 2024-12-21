<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Support\Caller\EventCaller;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignLabel extends SignWord
{

    protected mixed $label = null;

    /**
     * @param Closure|string $label
     * @return T
     */
    public function set(Closure|string $label)
    {
        $this->label = $label;
        return $this->sign;
    }

    /**
     * @param Closure $callback
     * @return T
     */
    public function using(Closure $callback)
    {
        $this->listen('using', $callback);
        return $this->sign;
    }

    protected function getEventOptionsOnUsing()
    {
        return [
            EventCaller::CALL_BUILDER,
        ];
    }

    /**
     * @param string|Closure $string
     * @return T
     */
    public function prefix(string|Closure $string)
    {
        $this->using(
            function (string $text) use ($string) {
                if ($string instanceof Closure) {
                    $args = func_get_args();
                    array_shift($args);

                    $string = $this->call($string, ...$args);
                }

                return $string . $text;
            },
        );

        return $this->sign;
    }

    protected function getEventOptionsOnPrefix()
    {
        return [
            EventCaller::CALL_BUILDER,
        ];
    }

    /**
     * @param string|Closure $string
     * @return T
     */
    public function suffix(string|Closure $string)
    {
        $this->using(
            function (string $text) use ($string) {
                if ($string instanceof Closure) {
                    $args = func_get_args();
                    array_shift($args);

                    $string = $this->call($string, ...$args);
                }

                return $text . $string;
            },
        );

        return $this->sign;
    }

    protected function getEventOptionsOnSuffix()
    {
        return [
            EventCaller::CALL_BUILDER,
        ];
    }


    public function getLabel(...$args): string
    {
        return $this->getLabelValue(false);
    }

    public function getNullableLabel(...$args): ?string
    {
        return $this->getLabelValue(true);
    }

    protected function getLabelValue(bool $nullable, ...$args): ?string
    {
        $label = $this->label instanceof Closure ?
            $this->call($this->label) :
            $this->label;

        $label = match (true) {
            is_null($label)   => null,
            is_string($label) => $label,
            default           => throw new \InvalidArgumentException(
                sprintf("Invalid label data, given [%s]", smartTypeOf($label)),
            ),
        };

        if ($label === null && $nullable) {
            return null;
        }

        return (string)$this->call('using', (string)$label, ...$args);
    }

}