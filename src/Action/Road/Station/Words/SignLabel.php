<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\Station;
use Mmb\Support\Caller\EventCaller;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignLabel extends SignWord
{

    protected mixed $label = null;

    public function set(Closure|string $label)
    {
        $this->label = $label;
        return $this->sign;
    }

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

    public function prefix(string|Closure $string)
    {
        $this->using(
            function ($text) use ($string) {
                if ($string instanceof Closure) {
                    $args = func_get_args();
                    array_shift($args);

                    $string = EventCaller::get('station')->fireSignAs($this, $string, ...$args);
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

    public function suffix(string|Closure $string)
    {
        $this->using(
            function ($text) use ($string) {
                if ($string instanceof Closure) {
                    $args = func_get_args();
                    array_shift($args);

                    $string = EventCaller::get('station')->fire($this, $string, ...$args);
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


    public function getLabel(Station $station, ...$args): string
    {
        return $this->getLabelValue($station, false, ...$args);
    }

    public function getNullableLabel(Station $station, ...$args): ?string
    {
        return $this->getLabelValue($station, true, ...$args);
    }

    protected function getLabelValue(Station $station, bool $nullable, ...$args): ?string
    {
        $label = $this->label instanceof Closure ?
            $this->fire($this->label, station: $station) :
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

        return (string)$this->fire('using', (string)$label, ...$args, station: $station);
    }

}