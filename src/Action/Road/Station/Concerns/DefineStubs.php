<?php

namespace Mmb\Action\Road\Station\Concerns;

use Mmb\Action\Road\Customize\Concerns\HasMenuCustomizing;
use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;
use Mmb\Support\Caller\EventCaller;
use Closure;

trait DefineStubs
{
    use HasMenuCustomizing;

    // todo

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
                        @array_shift($args);

                        $string = EventCaller::get('station')->fireSign($string, ...$args);
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
                        @array_shift($args);

                        $string = EventCaller::get('station')->fireSign($string, ...$args);
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
        $string = $station->fireSign($name, ...$args);

        return $station->fireSign($name . 'Using', $string, ...$args);
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
        if ($hasDefault = method_exists($this, $fn = 'onDefault' . $name))
        {
            $this->insertKey($group, $this->$fn(...), $name, $dx, $dy);
        }

        // testKey()
        $this->defineMethod(
            $name,
            function (Closure|false $callback, ?int $x = null, ?int $y = null) use (
                $name, $group, $dx, $dy
            )
            {
                $this->removeKey($group, $name);

                if ($callback)
                {
                    $this->insertKey($group, $callback, $name, $x ?? $dx, $y ?? $dy);
                }

                return $this;
            }
        );

        if ($hasDefault)
        {
            // testKeyDefault()
            $this->defineMethod(
                $name . 'Default',
                function (?int $x = null, ?int $y = null) use ($name, $group, $dx, $dy)
                {
                    $this->removeKey($group, $name);

                    $default = 'onDefault' . $name;
                    $this->insertKey($group, $this->$default(...), $name, $x ?? $dx, $y ?? $dy);

                    return $this;
                }
            );
        }

        $this->defineLabel($name . 'Label');
        $this->defineAction($name . 'Action');
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
            fn (Closure|false $callback) => $this->listen($name, $callback),
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
     * @param Menu    $menu
     * @param         ...$args
     * @return mixed
     */
    protected function getDefinedDynamicKey(Station $station, string $name, Menu $menu, ...$args)
    {
        return $station->fireSign($name, $menu, ...$args);
    }

}