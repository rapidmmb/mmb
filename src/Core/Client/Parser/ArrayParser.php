<?php

namespace Mmb\Core\Client\Parser;

use Illuminate\Support\Str;

class ArrayParser
{

    public ?array $hints = null;

    public function __construct(
        ?array $hints = null,
        public bool $snake = true,
        public bool $errorOnFail = false,
    )
    {
        if($hints !== null)
        {
            $this->setHints($hints);
        }
    }

    /**
     * Make new instance
     *
     * @param array|null $hints
     * @param bool       $snake
     * @param bool       $errorOnFail
     * @return static
     */
    public static function make(?array $hints = null, bool $snake = true, bool $errorOnFail = false)
    {
        return new static($hints, $snake, $errorOnFail);
    }

    /**
     * Set hints
     *
     * @param array $hints
     * @return void
     */
    public function setHints(array $hints)
    {
        $this->hints = $this->filterHints($hints);
    }

    /**
     * Merge hints to singleton instance
     *
     * @param array $hints
     * @return void
     */
    public static function mergeHints(array $hints)
    {
        $instance = app(static::class);

        $instance->hints = $instance->filterHints($hints) + $instance->hints;
    }

    /**
     * Add hint to singleton instance
     *
     * @param string $key
     * @param        $action
     * @return void
     */
    public static function on(string $key, $action)
    {
        static::mergeHints([$key => $action]);
    }

    /**
     * Filter hints
     *
     * @param array $hints
     * @return array
     */
    protected function filterHints(array $hints)
    {
        $result = [];
        foreach($hints as $key => $hint)
        {
            if(is_string($hint))
            {
                $hint = $this->getRealNameOf($hint);
            }

            $result[$this->getRealNameOf($key)] = $hint;
        }

        return $result;
    }

    /**
     * Get hints
     *
     * @return array
     */
    protected function getHints() : array
    {
        return $this->hints ?? [];
    }

    /**
     * Get real name of key
     *
     * @param string $key
     * @return string
     */
    protected function getRealNameOf(string $key)
    {
        return $this->snake ? Str::snake($key) : $key;
    }

    /**
     * Save other key
     *
     * @param string $key
     * @param        $value
     * @return mixed
     */
    protected function saveOtherKey(string $key, $value)
    {
        if($this->errorOnFail)
        {
            throw new \InvalidArgumentException("Invalid argument [$key]");
        }

        return $value;
    }

    /**
     * Normalize array by filtering
     *
     * @param array $values
     * @return array
     */
    public function normalize(array $values) : array
    {
        $result = [];
        $hints = $this->getHints();

        foreach($values as $key => $value)
        {
            $real = $this->getRealNameOf($key);

            if(($action = $hints[$real] ?? null) !== null)
            {
                if($action instanceof \Closure)
                {
                    $save = $action($key, $value, $real);
                    if(!is_null($save))
                    {
                        if(!is_array($save))
                        {
                            throw new \InvalidArgumentException("Argument parser for [$key] is not valid, expected array, returned " . gettype($save));
                        }

                        $result = $save + $result;
                    }
                }
                elseif(is_string($action))
                {
                    $result[$action] = $value;
                }
                elseif($action instanceof ArrayParser)
                {
                    if(!is_array($value))
                    {
                        throw new \TypeError("Argument [$key] should be array, given " . gettype($value));
                    }

                    $result[$real] = $action->normalize($value);
                }
            }
            else
            {
                $result[$real] = $this->saveOtherKey($key, $value);
            }
        }

        return $result;
    }

}
