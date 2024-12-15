<?php

namespace Mmb\Core\Client\Parser;

use Closure;
use Illuminate\Support\Str;
use Mmb\Core\Client\Client;

abstract class ArgsParserFactory
{

    protected array $args;
    protected array $argsDefaults;

    /**
     * Ready arguments
     *
     * @return void
     */
    private function readyArgs()
    {
        if(!isset($this->args))
        {
            $this->args = $this->default();
            $this->filter($this->args);
            $this->argsDefaults = [];
        }
    }

    /**
     * Add event for argument name
     *
     * @param string $name
     * @param        $value
     * @return void
     */
    public function on(string $name, $value)
    {
        $this->readyArgs();
        $snake = Str::snake($name);

        $this->args[$snake] = $this->filterMethod($value) + ($this->args[$snake] ?? []);
    }

    /**
     * Add event for argument name with custom method
     *
     * @param string       $name
     * @param string|array $method
     * @param              $value
     * @return void
     */
    public function onMethod(string $name, string|array $method, $value)
    {
        $this->readyArgs();

        $value = $this->filterReplace($value);
        $new = [];
        foreach(is_array($method) ? $method : [$method] as $method)
        {
            $new[strtolower($method)] = $value;
        }

        $this->on($name, $new);
    }

    /**
     * Set argument default value
     *
     * @param string $name
     * @param        $value
     * @return void
     */
    public function setDefault(string $name, $value)
    {
        $this->readyArgs();
        $snake = Str::snake($name);

        $this->argsDefaults[$snake] = $this->filterMethod($value) + ($this->argsDefaults[$snake] ?? []);
    }

    /**
     * Set argument default value in the special method
     *
     * @param string       $name
     * @param string|array $method
     * @param              $value
     * @return void
     */
    public function setDefaultOn(string $name, string|array $method, $value)
    {
        $this->readyArgs();

        $value = $this->filterReplace($value);
        $new = [];
        foreach(is_array($method) ? $method : [$method] as $method)
        {
            $new[strtolower($method)] = $value;
        }

        $this->setDefault($name, $new);
    }

    /**
     * Merge arguments
     *
     * @param array ...$items
     * @return void
     */
    public function merge(array ...$items)
    {
        $this->readyArgs();
        
        foreach($items as $items)
        {
            $this->filter($items);
            $this->args = array_replace($this->args, $items);
        }
    }

    /**
     * Filter argument group
     *
     * @param array $args
     * @return void
     */
    protected function filter(array &$args)
    {
        foreach($args as $name => $value)
        {
            $snake = Str::snake($name);
            if($snake != $name)
            {
                unset($args[$name]);
                $name = $snake;
            }

            $args[$name] = $this->filterMethod($value);
        }
    }

    /**
     * Filter method value
     *
     * @param $value
     * @return array|Closure[]
     */
    protected function filterMethod($value)
    {
        if(is_array($value))
        {
            $valueArray = [];
            foreach($value as $key => $v)
            {
                $valueArray[strtolower($key)] = $this->filterReplace($v);
            }

            return $valueArray;
        }
        else
        {
            return [
                '_' => $this->filterReplace($value)
            ];
        }
    }

    /**
     * Filter replace value
     *
     * @param $value
     * @return Closure|string
     */
    protected function filterReplace($value)
    {
        if(is_string($value))
        {
            if(@$value[0] == '@')
            {
                $func = substr($value, 1);
                return $this->$func(...);
            }
            else
            {
                return Str::snake($value);
            }
        }
        elseif(!($value instanceof Closure))
        {
            throw new \InvalidArgumentException("Invalid type of " . (gettype($value)) . ", expected string or closure");
        }

        return $value;
    }

    /**
     * Default arguments
     *
     * @return array
     */
    protected abstract function default() : array;

    /**
     * Convert mmb arguments to api arguments
     *
     * @param Client $request
     * @return array
     */
    public function normalize(Client $request)
    {
        $this->readyArgs();

        $result = [];
        foreach($request->args as $name => $value)
        {
            $snake = Str::snake($name);

            if(!is_null($map = $this->args[$snake] ?? null))
            {
                if(!is_array($map))
                {
                    throw new \InvalidArgumentException("Argument parser [$name] is not valid, required array, given " . gettype($map));
                }

                if(array_key_exists($request->lowerMethod(), $map))
                {
                    $action = $map[$request->lowerMethod()];
                }
                elseif(array_key_exists('_', $map))
                {
                    $action = $map['_'];
                }
                else
                {
                    throw new \InvalidArgumentException("Argument [$name] is not valid for method [{$request->method}]");;
                }

                // Closure action
                if($action instanceof Closure)
                {
                    $newValues = $action($request, $name, $value);
                    if(!is_null($newValues))
                    {
                        if(!is_array($newValues))
                        {
                            throw new \InvalidArgumentException("Argument parser for [$name] is not valid, closure must return array, returned " . gettype($newValues));
                        }

                        $result = $newValues + $result;
                    }
                }

                // Replace action
                elseif(is_string($action))
                {
                    $result[$action] = $value;
                }

                // Unexpected registered type
                else
                {
                    throw new \InvalidArgumentException("Argument parser for [$name] is not valid, required string or closure, given " . gettype($action));
                }
            }
            else
            {
                $result[$snake] = $value;
            }
        }

        foreach($this->argsDefaults as $argument => $defaults)
        {
            if(!is_array($defaults))
            {
                throw new \InvalidArgumentException("Argument parser [$argument] is not valid, required array, given " . gettype($defaults));
            }

            if(!array_key_exists($argument, $result))
            {
                if(array_key_exists($request->lowerMethod(), $defaults))
                {
                    $result[$argument] = value($defaults[$request->lowerMethod()], $request, $argument);
                }
                elseif(array_key_exists('_', $defaults))
                {
                    $result[$argument] = value($defaults['_'], $request, $argument);
                }
            }
        }

        return $result;
    }

}
