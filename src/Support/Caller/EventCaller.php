<?php

namespace Mmb\Support\Caller;

use Closure;

class EventCaller
{

    /**
     * Call the events linear from first to end
     *
     * Events cannot be canceled
     */
    public const CALL_LINEAR = 1 << 0;

    /**
     * Call the events linear from first to end
     *
     * Events will be cancelled when returns something like true
     */
    public const CALL_UNTIL_TRUE = 1 << 1;

    /**
     * Call the events linear from first to end
     *
     * Events will be cancelled when returns not null
     */
    public const CALL_UNTIL_NOT_NULL = 1 << 2;

    /**
     * Call the events linear from first to end
     *
     * Events will be cancelled when returns something like false
     */
    public const CALL_UNTIL_FALSE = 1 << 3;

    /**
     * Call the events linear from first to end
     *
     * Events will be cancelled when returns false
     */
    public const CALL_UNTIL_ACTUAL_FALSE = 1 << 4;

    /**
     * Call the events linear from first to end
     *
     * Returned value will be replaced by next first arguments and finally as result
     */
    public const CALL_BUILDER = 1 << 5;

    /**
     * Call the events linear from first to end
     *
     * Returned values will be replaced by all the arguments and finally as result
     */
    public const CALL_MULTIPLE_BUILDERS = 1 << 6;

    /**
     * Call the events linear from first to end
     *
     * Events called as pipeline
     */
    public const CALL_PIPELINE = 1 << 7;

    /**
     * Call only the last listener
     */
    public const CALL_ONLY_LAST = 1 << 8;


    /**
     * Call the events linear from end to first
     *
     * Events cannot be canceled
     */
    public const SORT_NORMAL = 1 << 0;

    /**
     * Call the events linear from end to first
     *
     * Events cannot be canceled
     */
    public const SORT_REVERSE = 1 << 1;


    /**
     * Call the default listener at last (expect event cancelled)
     */
    public const DEFAULT_ALWAYS = 1 << 0;

    /**
     * Call the default listener at first
     */
    public const DEFAULT_ALWAYS_FIRST = 1 << 1;

    /**
     * Call the default listener if not listener passed
     */
    public const DEFAULT_WHEN_NOT_LISTENING = 1 << 2;

    /**
     * Call the default listener if not listener passed
     */
    public const DEFAULT_PROXY = 1 << 3;


    /**
     * Automatically return a value
     */
    public const RETURN_AUTO = 1 << 0;

    /**
     * Return latest value
     */
    public const RETURN_LAST = 1 << 1;

    /**
     * Return first value that is like true
     */
    public const RETURN_FIRST_TRUE = 1 << 2;

    /**
     * Return all values as array
     */
    public const RETURN_ALL = 1 << 3;

    /**
     * Doesn't return value
     */
    public const RETURN_VOID = 1 << 4;


    /**
     * Fire an event
     *
     * @param array        $options
     * @param array        $listeners
     * @param array        $normalArgs
     * @param array        $dynamicArgs
     * @param Closure|null $defaultEvent
     * @return mixed
     */
    public static function fire(
        array    $options,
        array    $listeners,
        array    $normalArgs,
        array    $dynamicArgs = [],
        ?Closure $defaultEvent = null,
    )
    {
        $callType = $options['call'] ?? self::CALL_UNTIL_ACTUAL_FALSE;
        $sortType = $options['sort'] ?? self::SORT_NORMAL;
        $defaultType = $options['default'] ?? self::DEFAULT_ALWAYS;
        $returnType = $options['return'] ?? self::RETURN_AUTO;

        if ($sortType == self::SORT_REVERSE)
        {
            $listeners = array_reverse($listeners);
        }

        if (isset($defaultEvent))
        {
            switch ($defaultType)
            {
                case self::DEFAULT_ALWAYS:
                    if ($callType != self::CALL_ONLY_LAST || !$listeners)
                    {
                        $listeners[] = $defaultEvent;
                    }
                    break;

                case self::DEFAULT_ALWAYS_FIRST:
                    array_unshift($listeners, $defaultEvent);
                    break;

                case self::DEFAULT_WHEN_NOT_LISTENING:
                    if (!$listeners)
                    {
                        $listeners = [$defaultEvent];
                    }
                    break;

                case self::DEFAULT_PROXY:
                    $next = $listeners ?
                        function (...$args) use($options, $listeners, $dynamicArgs)
                        {
                            [$normalArgs, $dynamicArgs2] = Caller::splitArguments($args);

                            return self::fire($options, $listeners, $normalArgs, [...$dynamicArgs, ...$dynamicArgs2]);
                        } :
                        function ()
                        {
                            return null;
                        };

                    $listeners = [$defaultEvent];
                    array_unshift($normalArgs, $next);
                    break;
            }
        }


        if ($returnType == self::RETURN_ALL)
        {
            $return = [];
        }

        try
        {
            static::$eventDynamicStack[] = $dynamicArgs;

            switch ($callType)
            {
                case self::CALL_UNTIL_TRUE:
                    static::fireUntilTrue($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_UNTIL_NOT_NULL:
                    static::fireUntilNotNull($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_UNTIL_FALSE:
                    static::fireUntilFalse($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_UNTIL_ACTUAL_FALSE:
                    static::fireUntilActualFalse($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_BUILDER:
                    static::fireBuilder($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_MULTIPLE_BUILDERS:
                    static::fireMultipleBuilders($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_PIPELINE:
                    static::firePipleline($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_ONLY_LAST:
                    static::fireOnlyLast($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;

                case self::CALL_LINEAR:
                default:
                    static::fireLinear($listeners, $normalArgs, $dynamicArgs, $return, $returnType);
                    return $return;
            }
        }
        finally
        {
            array_pop(static::$eventDynamicStack);
        }
    }

    protected static function fireLinear(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        foreach ($listeners as $listener)
        {
            $temp = Caller::invoke($listener, $normalArgs, $dynamicArgs);
            static::mindReturn($returnType, $temp, $return);
        }

        return false;
    }

    protected static function fireOnlyLast(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        if ($listeners)
        {
            $return = Caller::invoke(last($listeners), $normalArgs, $dynamicArgs);
        }

        return true;
    }

    protected static function fireUntilTrue(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        foreach ($listeners as $listener)
        {
            $temp = Caller::invoke($listener, $normalArgs, $dynamicArgs);
            static::mindReturn($returnType, $temp, $return);

            if ($temp)
            {
                return true;
            }
        }

        return false;
    }

    protected static function fireUntilNotNull(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        foreach ($listeners as $listener)
        {
            $temp = Caller::invoke($listener, $normalArgs, $dynamicArgs);
            static::mindReturn($returnType, $temp, $return);

            if ($temp !== null)
            {
                return true;
            }
        }

        return false;
    }

    protected static function fireUntilFalse(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        foreach ($listeners as $listener)
        {
            $temp = Caller::invoke($listener, $normalArgs, $dynamicArgs);
            static::mindReturn($returnType, $temp, $return);

            if (!$temp)
            {
                return true;
            }
        }

        return false;
    }

    protected static function fireUntilActualFalse(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        foreach ($listeners as $listener)
        {
            $temp = Caller::invoke($listener, $normalArgs, $dynamicArgs);
            static::mindReturn($returnType, $temp, $return);

            if ($temp === false)
            {
                return true;
            }
        }

        return false;
    }

    protected static function fireBuilder(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        if (!$normalArgs)
        {
            throw new \InvalidArgumentException("Expected one argument for builder event");
        }

        $return = array_shift($normalArgs);

        foreach ($listeners as $listener)
        {
            $return = Caller::invoke($listener, [$return, ...$normalArgs], $dynamicArgs);
        }

        return false;
    }

    protected static function fireMultipleBuilders(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        foreach ($listeners as $listener)
        {
            $normalArgs = Caller::invoke($listener, $normalArgs, $dynamicArgs);
        }

        $return = $normalArgs;

        return false;
    }

    protected static function firePipleline(
        array $listeners,
        array $normalArgs,
        array $dynamicArgs,
              &$return,
              $returnType,
    )
    {
        if (!$normalArgs)
        {
            throw new \InvalidArgumentException("Expected one argument for pipeline event");
        }

        if (count($normalArgs) > 1)
        {
            throw new \InvalidArgumentException("Too many argument passed for pipeline event, expected 1");
        }

        $return = self::nextPipeline($listeners, 0, $dynamicArgs)(array_pop($normalArgs));

        return false;
    }

    protected static function nextPipeline(
        array &$listeners,
        int   $index,
        array &$dynamicArgs,
    )
    {
        if ($index < count($listeners))
        {
            return function ($value) use (&$listeners, $index, &$dynamicArgs)
            {
                $next = self::nextPipeline($listeners, $index + 1, $dynamicArgs);
                return Caller::invoke($listeners[$index], [$value, $next], $dynamicArgs);
            };
        }
        else
        {
            return static function ($value)
            {
                return $value;
            };
        }
    }

    protected static function mindReturn($returnType, $value, &$return)
    {
        switch ($returnType)
        {
            case self::RETURN_AUTO:
            case self::RETURN_LAST:
                $return = $value;
                break;

            case self::RETURN_FIRST_TRUE:
                if (is_null($return) && $value)
                {
                    $return = $value;
                }
                break;

            case self::RETURN_ALL:
                @$return[] = $value;
                break;
        }
    }

    protected static array $eventDynamicStack = [];

    /**
     * Get a dynamic argument from last calling
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        $stack = end(static::$eventDynamicStack);

        return array_key_exists($name, $stack) ? $stack[$name] : value($default);
    }

}