<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Encoding\Modes\Mode;
use Mmb\Support\Encoding\Modes\StringContent;
use Mmb\Support\Encoding\Text;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignMessage extends SignWord
{

    protected mixed $message = null;

    public function set(Closure|string|StringContent|array $message)
    {
        $this->message = $message;
        return $this->sign;
    }

    protected mixed $mode = null;

    public function mode(Closure|string|Mode $mode)
    {
        $this->mode = $mode;
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

    public function textUsing(Closure $callback)
    {
        $this->listen('textUsing', $callback);
        return $this->sign;
    }

    protected function getEventOptionsOnTextUsing()
    {
        return [
            EventCaller::CALL_BUILDER,
        ];
    }

    public function prefix(string|StringContent|Closure $string)
    {
        $this->textUsing(
            function (string $text, Mode $mode) use ($string) {
                if ($string instanceof Closure) {
                    $args = func_get_args();
                    array_shift($args);
                    array_shift($args);

                    $string = $this->call($string, ...$args, mode: $mode);
                }

                return $mode->string($string) . $text;
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

    public function suffix(string|StringContent|Closure $string)
    {
        $this->textUsing(
            function (string $text, Mode $mode) use ($string) {
                if ($string instanceof Closure) {
                    $args = func_get_args();
                    array_shift($args);
                    array_shift($args);

                    $string = $this->call($string, ...$args, mode: $mode);
                }

                return $text . $mode->string($string);
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


    public function getMessage(...$args): array
    {
        $mode = $this->mode instanceof Closure ?
            $this->call($this->mode, ...$args) :
            $this->mode;

        $mode = match (true) {
            is_string($mode)      => Text::mode($mode),
            is_null($mode)        => Text::mode('HTML'),
            $mode instanceof Mode => $mode,
            default               => throw new \InvalidArgumentException(
                sprintf("Invalid message mode, given [%s]", smartTypeOf($mode)),
            ),
        };

        $message = $this->message instanceof Closure ?
            $this->call($this->message, ...$args) :
            $this->message;

        $message = match (true) {
            is_null($message)                 => [],
            is_string($message)               => ['text' => $mode->string($message)],
            $message instanceof StringContent => ['text' => $message->toString()],
            is_array($message)                => $message,
            $message instanceof Arrayable     => $message->toArray(),
            default                           => throw new \InvalidArgumentException(
                sprintf("Invalid message data, given [%s]", smartTypeOf($message)),
            ),
        };

        $message = (array)$this->call('using', $message, ...$args, mode: $mode);

        $message['text'] = (string)$this->call(
            'textUsing',
            $message['text'] ?? '',
            ...$args,
            mode: $mode,
        );

        return $message;
    }

}