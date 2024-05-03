<?php

namespace Mmb\Core\Builder;

use Closure;
use Illuminate\Support\Str;
use Mmb\Core\Bot;
use Mmb\Support\Macroable\ExtendableMacroable;

class ApiBuilder
{
    use ExtendableMacroable;

    public function __construct(
        Bot    $bot,
        string $method = null,
        array  $args = [],
    )
    {
        $this->bot = $bot;
        $this->method = $method;
        $this->args = $args;

        $this->initialize();
    }

    protected function initialize()
    {
    }

    public static function make(
        Bot    $bot,
        string $method = null,
        array  $args = []
    )
    {
        return new static($bot, $method, $args);
    }

    /**
     * Target api bot
     *
     * @var Bot
     */
    public Bot $bot;

    /**
     * Request arguments
     *
     * @var array
     */
    public array $args = [];

    /**
     * Request method
     *
     * @var string
     */
    public ?string $method = null;

    /**
     * Get request method
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set request method
     *
     * @param string|null $method
     * @return $this
     */
    public function method(?string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set argument
     *
     * @param string $key
     * @param        $value
     * @return $this
     */
    public function put(string $key, $value)
    {
        $this->args[Str::snake($key)] = $value;
        return $this;
    }

    /**
     * Get argument
     *
     * @param string $key
     * @param        $default
     * @return mixed
     */
    public function old(string $key, $default = null)
    {
        return $this->args[Str::snake($key)] ?? value($default);
    }

    /**
     * Send request
     *
     * @param ?string $method
     * @param array   $args
     * @return false
     */
    public function request(?string $method = null, array $args = [])
    {
        return $this->bot->request($method ?? $this->getMethod(), $args + $this->args);
    }

    /**
     * Ignore errors
     *
     * @param $condition
     * @return $this
     */
    public function ignore($condition = true)
    {
        return $this->put('ignore', (bool) value($condition));
    }

    /**
     * @param        $data
     * @param array  $classes
     * @param string $expected
     * @return mixed
     */
    public function expect($data, array $classes, string $expected)
    {
        if($data === null)
        {
            return null;
        }

        if(is_object($data))
        {
            foreach($classes as $class => $property)
            {
                if($data instanceof $class)
                {
                    if($property instanceof Closure)
                    {
                        return $property($data);
                    }

                    return data_get($data, $property);
                }
            }

            throw new \TypeError("Expected $expected, given [" . get_class($data) . "]");
        }

        return $data;
    }

    /**
     * Call callback if condition is true
     *
     * @param $condition
     * @param $callback
     * @return $this
     */
    public function when($condition, $callback)
    {
        if(value($condition))
        {
            $callback();
        }

        return $this;
    }

    /**
     * Call callback if condition is false
     *
     * @param $condition
     * @param $callback
     * @return $this
     */
    public function until($condition, $callback)
    {
        if(!value($condition))
        {
            $callback();
        }

        return $this;
    }

}