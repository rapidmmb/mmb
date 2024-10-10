<?php

namespace Mmb\Action\Road\Station\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Mmb\Action\Contracts\Menuable;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\WeakSign;
use Mmb\Action\Section\Menu;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Encoding\Modes\Mode;
use Mmb\Support\Encoding\Modes\StringContent;
use Mmb\Support\Encoding\Text;

trait DefineStubs
{
    
    /**
     * Define a label
     *
     * ```
     * method $this name(Closure $callback)
     * method $this nameUsing(Closure $callback)
     * method $this namePrefix(string|Closure $string)
     * method $this nameSuffix(string|Closure $string)
     * ```
     *
     * @param string $name
     * @return void
     */
    protected final function defineLabel(string $name)
    {
        // testLabel(Closure $callback)
        $this->defineMethod(
            $name,
            fn (Closure $callback) => $this->listen($name, $callback)
        );
        $this->defineEvent(
            $name,
            [
                'call' => EventCaller::CALL_UNTIL_NOT_NULL,
                'sort' => EventCaller::SORT_REVERSE,
            ]
        );

        $nameUsing = $name . 'Using';

        // testLabelUsing(Closure $callback)
        $this->defineMethod(
            $nameUsing,
            fn (Closure $callback) => $this->listen($nameUsing, $callback)
        );
        $this->defineEvent(
            $nameUsing,
            [
                'call' => EventCaller::CALL_BUILDER,
            ]
        );

        // testLabelPrefix(string|Closure $string)
        $this->defineMethod(
            $name . 'Prefix',
            fn (string|Closure $string) => $this->listen(
                $nameUsing,
                function ($text) use ($string)
                {
                    if ($string instanceof Closure)
                    {
                        $args = func_get_args();
                        array_shift($args);

                        $string = EventCaller::get('station')->fireSignAs($this, $string, ...$args);
                    }

                    return $string . $text;
                },
            )
        );

        // testLabelSuffix(string|Closure $string)
        $this->defineMethod(
            $name . 'Suffix',
            fn (string|Closure $string) => $this->listen(
                $nameUsing,
                function ($text) use ($string)
                {
                    if ($string instanceof Closure)
                    {
                        $args = func_get_args();
                        array_shift($args);

                        $string = EventCaller::get('station')->fireSignAs($this, $string, ...$args);
                    }

                    return $text . $string;
                },
            )
        );
    }

    /**
     * Get a defined label value
     *
     * @param Station $station
     * @param string  $name
     * @param         ...$args
     * @return string
     */
    protected function getDefinedLabel(Station $station, string $name, ...$args) : string
    {
        $string = $this->fireBy($station, $name, ...$args);

        return $this->fireBy($station, $name . 'Using', $string, ...$args);
    }

    /**
     * Get a defined label value
     *
     * @param Station $station
     * @param string  $name
     * @param         ...$args
     * @return ?string
     */
    protected function getDefinedNullableLabel(Station $station, string $name, ...$args) : ?string
    {
        $string = $this->fireBy($station, $name, ...$args);

        if ($string === null)
            return null;

        return $this->fireBy($station, $name . 'Using', $string, ...$args);
    }

    /**
     * Define an action
     *
     * ```
     * method $this name(Closure $action)
     * ```
     *
     * @param string $name
     * @return void
     */
    protected final function defineAction(string $name)
    {
        // test(Closure $callback)
        $this->defineMethod(
            $name,
            fn (Closure $callback) => $this->listen($name, $callback)
        );
        $this->defineEvent(
            $name,
            [
                'call'    => EventCaller::CALL_UNTIL_ACTUAL_FALSE,
                'default' => EventCaller::DEFAULT_WHEN_NOT_LISTENING,
            ]
        );
    }

    /**
     * Define a Key
     *
     * ```
     * method $this name(Closure|false $callback, int $x = DEFAULT_X, int $y = DEFAULT_Y)
     * method $this nameDefault(int $x = DEFAULT_X, int $y = DEFAULT_Y)
     * method $this nameAction(Closure $action)
     * method $this nameLabel(Closure $callback)
     * method $this nameLabelUsing(Closure $callback)
     * method $this nameLabelPrefix(string|Closure $string)
     * method $this nameLabelSuffix(string|Closure $string)
     * ```
     *
     * @param string $name
     * @param string $group
     * @param int    $dx
     * @param int    $dy
     * @return void
     */
    protected final function defineKey(string $name, string $group, int $dx, int $dy)
    {
        $this->defineProxyKey($this, $name, $group, $dx, $dy);
    }

    /**
     * Define a Key in other sign
     *
     * ```
     * method $this name(Closure|false $callback, int $x = DEFAULT_X, int $y = DEFAULT_Y)
     * method $this nameDefault(int $x = DEFAULT_X, int $y = DEFAULT_Y)
     * method $this nameAction(Closure $action)
     * method $this nameLabel(Closure $callback)
     * method $this nameLabelUsing(Closure $callback)
     * method $this nameLabelPrefix(string|Closure $string)
     * method $this nameLabelSuffix(string|Closure $string)
     * ```
     *
     * @param WeakSign $in
     * @param string   $name
     * @param string   $group
     * @param int      $dx
     * @param int      $dy
     * @return void
     */
    protected function defineProxyKey(WeakSign $in, string $name, string $group, int $dx, int $dy)
    {
        if ($hasDefault = method_exists($this, $fn = 'onDefault' . $name))
        {
            $in->insertKey($group, $this->$fn(...), $name, $dx, $dy);
        }

        $hasProxy = method_exists($this, $proxyMethod = 'on' . $name . 'Via');

        // testKey()
        $this->defineMethod(
            $name,
            function (Closure|false $callback, ?int $x = null, ?int $y = null) use (
                $name, $group, $dx, $dy, $hasProxy, $proxyMethod, $in
            )
            {
                $in->removeKey($group, $name);

                if ($callback)
                {
                    if ($hasProxy)
                    {
                        $callback = function (...$args) use ($callback, $proxyMethod)
                        {
                            return Caller::invoke(
                                $this->$proxyMethod(...),
                                [
                                    fn (...$args) => Caller::invoke($callback, $args, EventCaller::all()),
                                    ...$args,
                                ],
                                EventCaller::all()
                            );
                        };
                    }

                    $in->insertKey($group, $callback, $name, $x ?? $dx, $y ?? $dy);
                }

                return $this;
            }
        );

        if ($hasDefault)
        {
            // testKeyDefault()
            $this->defineMethod(
                $name . 'Default',
                function (?int $x = null, ?int $y = null) use ($name, $group, $dx, $dy, $in)
                {
                    $in->removeKey($group, $name);

                    $default = 'onDefault' . $name;
                    $in->insertKey($group, $this->$default(...), $name, $x ?? $dx, $y ?? $dy);

                    return $this;
                }
            );
        }

        $this->defineLabel($name . 'Label');
        $this->defineAction($name . 'Action');
    }

    protected function shutdownProxyKey(WeakSign $in, string $name, string $group)
    {
        $in->removeKey($group, $name);
    }

    /**
     * Define a dynamic key
     *
     *  ```
     *  method $this name(Closure|false $callback)
     *  method $this nameAction(Closure $action)
     *  method $this nameLabel(Closure $callback)
     *  method $this nameLabelUsing(Closure $callback)
     *  method $this nameLabelPrefix(string|Closure $string)
     *  method $this nameLabelSuffix(string|Closure $string)
     *  ```
     *
     * @param string $name
     * @return void
     */
    protected final function defineDynamicKey(string $name)
    {
        // test()
        $this->defineMethod(
            $name,
            fn (Closure|false $callback) => $this->listen($name, $callback ?: fn () => null),
        );
        $this->defineEvent(
            $name,
            [
                'call' => EventCaller::CALL_UNTIL_TRUE,
                'sort' => EventCaller::SORT_REVERSE,
            ]
        );

        $this->defineLabel($name . 'Label');
        $this->defineAction($name . 'Action');
    }

    /**
     * Get defined dynamic key
     *
     * @param Station $station
     * @param string  $name
     * @param Menuable    $menu
     * @param         ...$args
     * @return mixed
     */
    protected function getDefinedDynamicKey(Station $station, string $name, Menuable $menu, ...$args)
    {
        return $this->fireBy($station, $name, $menu, ...$args);
    }

    /**
     * Define a message
     *
     * ```
     * method $this name(Closure|string|StringContent|array $message)
     * method $this nameMode(Closure|string|Mode $mode)
     * method $this nameUsing(Closure $callback)
     * method $this nameTextUsing(Closure $callback)
     * method $this namePrefix(string|StringContent|Closure $string)
     * method $this nameSuffix(string|StringContent|Closure $string)
     * ```
     *
     * @param string $name
     * @return void
     */
    protected function defineMessage(string $name)
    {
        // testMessage()
        $this->defineMethod(
            $name,
            fn (Closure|string|StringContent|array $message) => $this->listen(
                $name,
                match (true)
                {
                    $message instanceof Closure => $message,
                    is_array($message)          => function (Mode $mode) use ($message)
                    {
                        if (isset($message['mode']))
                        {
                            throw new \InvalidArgumentException("Can't pass the [mode]");
                        }

                        $message['text'] = $mode->string($message['text'] ?? '');
                        return $message;
                    },
                    default                     => fn (Mode $mode) => $mode->string($message)
                }
            )
        );
        $this->defineEvent(
            $name,
            [
                'call' => EventCaller::CALL_UNTIL_NOT_NULL,
                'sort' => EventCaller::SORT_REVERSE,
            ]
        );

        // testMessageMode()
        $this->defineMethod(
            $name . 'Mode',
            fn (Closure|string|Mode $mode) => $this->listen(
                $name . 'Mode',
                $mode instanceof Closure ? $mode : fn () => $mode,
            )
        );
        $this->defineEvent(
            $name . 'Mode',
            [
                'call' => EventCaller::CALL_UNTIL_NOT_NULL,
                'sort' => EventCaller::SORT_REVERSE,
            ]
        );

        $nameUsing = $name . 'Using';
        $nameTextUsing = $name . 'TextUsing';

        // testMessageUsing(Closure $callback)
        $this->defineMethod(
            $nameUsing,
            fn (Closure $callback) => $this->listen($nameUsing, $callback)
        );
        $this->defineEvent(
            $nameUsing,
            [
                'call' => EventCaller::CALL_BUILDER,
            ]
        );

        // testMessageTextUsing(Closure $callback)
        $this->defineMethod(
            $nameTextUsing,
            fn (Closure $callback) => $this->listen($nameTextUsing, $callback)
        );
        $this->defineEvent(
            $nameTextUsing,
            [
                'call' => EventCaller::CALL_BUILDER,
            ]
        );

        // testMessagePrefix()
        $this->defineMethod(
            $name . 'Prefix',
            fn (string|Closure|StringContent $string) => $this->listen(
                $nameTextUsing,
                function ($text, Mode $mode) use ($string)
                {
                    if ($string instanceof Closure)
                    {
                        $args = func_get_args();
                        array_shift($args);

                        $string = EventCaller::get('station')->fireSignAs($this, $string, ...$args, mode: $mode);
                    }

                    return $mode->string($string) . $text;
                },
            )
        );

        // testMessageSuffix()
        $this->defineMethod(
            $name . 'Suffix',
            fn (string|Closure $string) => $this->listen(
                $nameTextUsing,
                function ($text, Mode $mode) use ($string)
                {
                    if ($string instanceof Closure)
                    {
                        $args = func_get_args();
                        array_shift($args);

                        $string = EventCaller::get('station')->fireSignAs($this, $string, ...$args, mode: $mode);
                    }

                    return $text . $mode->string($string);
                },
            )
        );
    }

    /**
     * Get defined message value
     *
     * @param Station $station
     * @param string  $name
     * @param         ...$args
     * @return array
     */
    protected function getDefinedMessage(Station $station, string $name, ...$args) : array
    {
        $mode = $this->fireBy($station, $name . 'Mode', ...$args);
        $mode = match (true)
        {
            is_string($mode)      => Text::mode($mode),
            is_null($mode)        => Text::mode('HTML'),
            $mode instanceof Mode => $mode,
            default               => throw new \InvalidArgumentException(
                sprintf("Invalid message mode, given [%s]", smartTypeOf($mode))
            ),
        };

        $message = $this->fireBy($station, $name, ...$args, mode: $mode);
        $message = match (true)
        {
            is_null($message)                 => [],
            is_string($message)               => ['text' => $message],
            $message instanceof StringContent => ['text' => $message->toString()],
            is_array($message)                => $message,
            $message instanceof Arrayable     => $message->toArray(),
            default                           => throw new \InvalidArgumentException(
                sprintf("Invalid message data, given [%s]", smartTypeOf($message))
            ),
        };

        $message = (array) $this->fireBy($station, $name . 'Using', $message, ...$args, mode: $mode);

        $message['text'] = (string) $this->fireBy(
            $station,
            $name . 'TextUsing',
            $message['text'] ?? '',
            ...$args,
            mode: $mode
        );

        return $message;
    }

}