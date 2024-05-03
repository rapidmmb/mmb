<?php

namespace Mmb\Support\AttributeLoader;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Support\Caller\ParameterPassingInstead;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

trait HasAttributeLoader
{

    private static array $_class_attributes      = [];
    private static array $_properties_attributes = [];
    private static array $_methods_attributes    = [];
    private static array $_parameter_attributes  = [];

    /**
     * Get current class attributes
     *
     * @return array
     */
    public static function getClassAttributes() : array
    {
        if(isset(static::$_class_attributes[static::class]))
        {
            return static::$_class_attributes[static::class];
        }

        $attrs = [];
        foreach((new ReflectionClass(static::class))->getAttributes() as $attr)
        {
            $attrs[] = $attr->newInstance();
        }

        return static::$_class_attributes[static::class] = $attrs;
    }

    /**
     * Get current class attributes
     *
     * @template T
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getClassAttributesOf(string $class) : array
    {
        return array_filter(static::getClassAttributes(), fn($attr) => $attr instanceof $class);
    }

    /**
     * Get property attributes
     *
     * @param string $property
     * @return array
     */
    public static function getPropertyAttributes(string $property) : array
    {
        if(isset(static::$_class_attributes[static::class][$property]))
        {
            return static::$_class_attributes[static::class][$property];
        }

        $attrs = [];
        foreach((new ReflectionProperty(static::class, $property))->getAttributes() as $attr)
        {
            $attrs[] = $attr->newInstance();
        }

        return @static::$_class_attributes[static::class][$property] = $attrs;
    }

    /**
     * Get property attributes
     *
     * @template T
     * @param string          $property
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getPropertyAttributesOf(string $property, string $class) : array
    {
        return array_filter(static::getPropertyAttributes($property), fn($attr) => $attr instanceof $class);
    }

    /**
     * Get method attributes
     *
     * @param string $method
     * @return array
     */
    public static function getMethodAttributes(string $method) : array
    {
        $lower = strtolower($method);
        if(isset(static::$_class_attributes[static::class][$lower]))
        {
            return static::$_class_attributes[static::class][$lower];
        }

        $attrs = [];
        foreach((new ReflectionMethod(static::class, $method))->getAttributes() as $attr)
        {
            $attrs[] = $attr->newInstance();
        }

        return @static::$_class_attributes[static::class][$lower] = $attrs;
    }

    /**
     * Get method attributes
     *
     * @template T
     * @param string          $method
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getMethodAttributesOf(string $method, string $class) : array
    {
        return array_filter(static::getMethodAttributes($method), fn($attr) => $attr instanceof $class);
    }


    /**
     * Get class attribute
     *
     * @template T
     * @param class-string<T> $class
     * @return ?T
     */
    public static function getClassAttributeOf(string $class)
    {
        return @static::getClassAttributesOf($class)[0];
    }

    /**
     * Get class attribute
     *
     * @template T
     * @param string          $property
     * @param class-string<T> $class
     * @return ?T
     */
    public static function getPropertyAttributeOf(string $property, string $class)
    {
        return @static::getPropertyAttributesOf($property, $class)[0];
    }

    /**
     * Get class attribute
     *
     * @template T
     * @param string          $method
     * @param class-string<T> $class
     * @return ?T
     */
    public static function getMethodAttributeOf(string $method, string $class)
    {
        return @static::getMethodAttributesOf($method, $class)[0];
    }


    /**
     * Get all method parameters attributes
     *
     * @param string $method
     * @return array<string,mixed>
     */
    public static function getAllParameterAttributes(string $method)
    {
        $lower = strtolower($method);
        if(isset(static::$_parameter_attributes[static::class][$lower]))
        {
            return static::$_parameter_attributes[static::class][$lower];
        }

        $all = [];
        foreach((new ReflectionMethod(static::class, $method))->getParameters() as $parameter)
        {
            $attrs = array_map(fn($attr) => $attr->newInstance(), $parameter->getAttributes());
            $all[$parameter->getName()] = $attrs;
        }

        return @static::$_parameter_attributes[static::class][$lower] = $all;
    }

    /**
     * Get method parameter attributes
     *
     * @param string $method
     * @param string $parameter
     * @return array
     */
    public static function getParameterAttributes(string $method, string $parameter)
    {
        return static::getAllParameterAttributes($method)[$parameter];
    }

    /**
     * Get method parameter attributes
     *
     * @template T
     * @param string $method
     * @param string $parameter
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getParameterAttributesOf(string $method, string $parameter, string $class)
    {
        return array_filter(static::getParameterAttributes($method, $parameter), fn($attr) => $attr instanceof $class);
    }

    /**
     * Get method parameter attribute
     *
     * @template T
     * @param string $method
     * @param string $parameter
     * @param class-string<T> $class
     * @return T
     */
    public static function getParameterAttributeOf(string $method, string $parameter, string $class)
    {
        return @static::getParameterAttributesOf($method, $parameter, $class)[0];
    }

    public static function getNormalizedCallingMethod(string $method, array $args, Closure $callback = null)
    {
        $parametersAll = (new ReflectionMethod(static::class, $method))->getParameters();
        $parameters = $parametersAll;
        array_shift($parameters);

        foreach($args as $key => $value)
        {
            if(is_int($key))
            {
                if($first = Arr::first($parameters))
                {
                    $args[$first->getName()] = $value;
                    unset($args[$key]);
                }
            }
        }

        foreach($parameters as $parameter)
        {
            $value = array_key_exists($parameter->getName(), $args) ?
                $args[$parameter->getName()] :
                $parameter->getDefaultValue();

            if($instead = static::getParameterAttributeOf($method, $parameter->getName(), ParameterPassingInstead::class))
            {
                $value = $instead->getInsteadOf($value);
            }

            if($callback)
            {
                $callback($parameter, $value);
            }
        }

        return $args;
    }

}
