<?php

namespace Mmb\Core;

use ArrayAccess;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mmb\Core\Traits\HasBot;
use Mmb\Support\Macroable\ExtendableMacroable;
use Mmb\Support\Serialize\Shortable;

abstract class Data implements Arrayable, Jsonable, ArrayAccess, Shortable
{
    use HasBot, ExtendableMacroable;

    protected array $realData;
    protected array $allData;

    public function __construct(array $data, Bot $bot = null, bool $trustedData = false)
    {
        $this->setTargetBot($bot);
        $this->realData = $data;
        $this->initialize($data, $trustedData);
    }

    public static function make($data, Bot $bot = null, bool $trustedData = false)
    {
        if(!is_array($data))
        {
            return null;
        }

        return new static($data, $bot, $trustedData);
    }

    protected function dataCasts() : array
    {
        return [];
    }

    protected function dataRules() : array
    {
        return [];
    }

    protected function dataShortAccess() : array
    {
        return [];
    }

    protected array $_shortAccess;

    protected final function getDataShortAccess(string $name = null)
    {
        if(!isset($this->_shortAccess))
        {
            $this->_shortAccess = [];
            foreach($this->dataShortAccess() as $alias => $to)
            {
                $this->_shortAccess[strtolower($alias)] = $to;
            }
        }

        if(isset($name))
        {
            if(array_key_exists($name, $this->allData))
            {
                return $name;
            }

            return @$this->_shortAccess[strtolower($name)];
        }

        return $this->_shortAccess;
    }


    protected function initialize(array $data, bool $trustedData)
    {
        if(!$trustedData)
        {
            // $data = Validator::make($data, $this->dataRules())->validate();
        }

        $this->allData = $this->setCleanData(
            $this->castData($data, $trustedData)
        );
    }

    protected function castData(array $data, bool $trustedData = false)
    {
        foreach($this->dataCasts() as $name => $cast)
        {
            if(!isset($data[$name]))
            {
                $data[$name] = null;
                continue;
            }

            $data[$name] = $this->castSingleData($data[$name], $cast, $trustedData);
        }

        return $data;
    }

    protected function castSingleData($value, $cast, bool $trustedData)
    {
        if($value === null)
        {
            return null;
        }
        elseif(class_exists($cast))
        {
            if($value instanceof $cast)
            {
                return $value;
            }
            elseif(is_object($value))
            {
                throw new \InvalidArgumentException("Expected [$cast] type casting, given [" . get_class($value) . "]");
            }
            elseif(method_exists($cast, 'make'))
            {
                return $cast::make($value, $this->targetBot, $trustedData);
            }
            else
            {
                return new $cast(is_array($value) ? $value : [], $this->targetBot, $trustedData);
            }
        }
        elseif(is_array($cast))
        {
            return collect($value)->map(fn($data) => $this->castSingleData($data, $cast[0], $trustedData));
        }
        else
        {
            return match ($cast)
            {
                'int', 'long'     => (int) $value,
                'double', 'float' => (double) $value,
                'string'          => (string) $value,
                'array'           => (array) $value,
                'bool'            => (bool) $value,
                'date'            => new Carbon($value),
                default           => throw new \TypeError("Cast [$cast] is not found"),
            };
        }
    }

    protected function setCleanData(array $data)
    {
        foreach($data as $name => $value)
        {
            if(property_exists($this, $name))
            {
                $this->$name = $value;
                unset($data[$name]);
            }
        }

        return $data;
    }

    public function getFullData()
    {
        $data = $this->allData;

        foreach($this->dataCasts() as $name => $value)
        {
            if(property_exists($this, $name))
            {
                $data[$name] = $this->$name;
            }
        }

        return $data;
    }

    public function toArray()
    {
        $data = $this->getFullData();

        foreach($this->dataCasts() as $name => $value)
        {
            if(!isset($data[$name]))
                continue;

            $value = &$data[$name];

            if($value instanceof Data)
            {
                $value = $value->toArray();
            }
            elseif($value instanceof Arrayable)
            {
                $value = $value->toArray();
            }
            else
            {
                # Auto cast
            }
        }

        return $data;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function __get(string $name)
    {
        if(method_exists($this, $fn = "get{$name}Attribute"))
        {
            return $this->$fn();
        }

        $access = $this->getDataShortAccess($name) ?? $this->getDataShortAccess(Str::snake($name));
        if($access === null)
        {
            error_log("Undefined property [$name] on [" . static::class . "]");
            return null;
        }

        return $this->allData[$access];
    }

    public function __set(string $name, $value) : void
    {
        $access = $this->getDataShortAccess($name) ?? $name;

        $this->$access = $value;
    }

    protected function mergeMultiple(array $valueArgs, array $fixedArgs)
    {
        $args = [];
        foreach($valueArgs as $key => $value)
        {
            if(is_array($value))
            {
                $args = $value + $args;
            }
            elseif($value !== null)
            {
                $args[$key] = $value;
            }
        }

        return $fixedArgs + $args;
    }

    protected $_caches = [];

    /**
     * @param string  $name
     * @param Closure $maker
     * @return mixed
     */
    protected function makeCache(string $name, Closure $maker)
    {
        return array_key_exists($name, $this->_caches) ?
            $this->_caches[$name] :
            $this->_caches[$name] = $maker();
    }


    public function offsetExists(mixed $offset) : bool
    {
        return @$this->__get($offset) !== null;
    }

    public function offsetGet(mixed $offset) : mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset(mixed $offset) : void
    {
        $this->__set($offset, null);
    }

    public function shortSerialize() : array
    {
        return Arr::whereNotNull($this->realData);
    }

    public function shortUnserialize(array $data) : void
    {
        $this->realData = $data;
        $this->initialize($data, true);
    }
}
