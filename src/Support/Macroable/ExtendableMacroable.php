<?php

namespace Mmb\Support\Macroable;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Traits\Macroable;

trait ExtendableMacroable
{
    use Macroable;

    /**
     * Register a custom macro.
     *
     * @param  string  $name
     * @param  object|callable  $macro
     * @return void
     */
    public static function macro($name, $macro)
    {
        @static::$macros[static::class][$name] = $macro;
    }

    /**
     * Checks if macro is registered.
     *
     * @param  string  $name
     * @return bool
     */
    public static function hasMacro($name)
    {
        return static::_getMacroCallable($name) !== false;
    }

    /**
     * Find macro from current and parent classes
     *
     * @param $name
     * @return mixed
     */
    private static function _getMacroCallable($name)
    {
        $class = static::class;
        do
        {
            if(isset(static::$macros[$class][$name]))
            {
                return static::$macros[$class][$name];
            }
        }
        while(($class = get_parent_class($class)) !== false);

        return false;
    }

    /**
     * Flush the existing macros.
     *
     * @return void
     */
    public static function flushMacros()
    {
        static::$macros[static::class] = [];
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if (! ($macro = static::_getMacroCallable($method))) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (! ($macro = static::_getMacroCallable($method))) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }
}