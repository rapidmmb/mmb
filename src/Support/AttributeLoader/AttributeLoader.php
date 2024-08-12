<?php

namespace Mmb\Support\AttributeLoader;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Support\Caller\ParameterPassingInstead;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class AttributeLoader
{

    private static array $_class_attributes      = [];
    private static array $_properties_attributes = [];
    private static array $_methods_attributes    = [];
    private static array $_parameter_attributes  = [];

    /**
     * Get current class attributes
     *
     * @param string|object $target
     * @return array
     */
    public static function getClassAttributes(string|object $target) : array
    {
        if (is_object($target))
        {
            $target = get_class($target);
        }

        if(isset(static::$_class_attributes[$target]))
        {
            return static::$_class_attributes[$target];
        }

        $attrs = [];
        foreach((new ReflectionClass($target))->getAttributes() as $attr)
        {
            $attrs[] = $attr->newInstance();
        }

        return static::$_class_attributes[$target] = $attrs;
    }

    /**
     * Get current class attributes
     *
     * @template T
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getClassAttributesOf(string|object $target, string $class) : array
    {
        return array_filter(static::getClassAttributes($target), fn($attr) => $attr instanceof $class);
    }

    /**
     * Get property attributes
     *
     * @param string|object $target
     * @param string        $property
     * @return array
     */
    public static function getPropertyAttributes(string|object $target, string $property) : array
    {
        if (is_object($target))
        {
            $target = get_class($target);
        }

        if(isset(static::$_properties_attributes[$target][$property]))
        {
            return static::$_properties_attributes[$target][$property];
        }

        $attrs = [];

        foreach((new ReflectionProperty($target, $property))->getAttributes() as $attr)
        {
            $attrs[] = $attr->newInstance();
        }

        return @static::$_properties_attributes[$target][$property] = $attrs;
    }

    /**
     * Get property attributes
     *
     * @template T
     * @param string|object   $target
     * @param string          $property
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getPropertyAttributesOf(string|object $target, string $property, string $class) : array
    {
        return array_filter(static::getPropertyAttributes($target, $property), fn($attr) => $attr instanceof $class);
    }

    /**
     * Get method attributes
     *
     * @param string|object $target
     * @param string        $method
     * @return array
     */
    public static function getMethodAttributes(string|object $target, string $method) : array
    {
        if (is_object($target))
        {
            $target = get_class($target);
        }

        $lower = strtolower($method);
        if(isset(static::$_methods_attributes[$target][$lower]))
        {
            return static::$_methods_attributes[$target][$lower];
        }

        $attrs = [];
        foreach((new ReflectionMethod($target, $method))->getAttributes() as $attr)
        {
            $attrs[] = $attr->newInstance();
        }

        return @static::$_methods_attributes[$target][$lower] = $attrs;
    }

    /**
     * Get method attributes
     *
     * @template T
     * @param string|object   $target
     * @param string          $method
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getMethodAttributesOf(string|object $target, string $method, string $class) : array
    {
        return array_filter(static::getMethodAttributes($target, $method), fn($attr) => $attr instanceof $class);
    }


    /**
     * Get class attribute
     *
     * @template T
     * @param class-string<T> $class
     * @return ?T
     */
    public static function getClassAttributeOf(string|object $target, string $class)
    {
        return @static::getClassAttributesOf($target, $class)[0];
    }

    /**
     * Get class attribute
     *
     * @template T
     * @param string|object   $target
     * @param string          $property
     * @param class-string<T> $class
     * @return ?T
     */
    public static function getPropertyAttributeOf(string|object $target, string $property, string $class)
    {
        return @static::getPropertyAttributesOf($target, $property, $class)[0];
    }

    /**
     * Get class attribute
     *
     * @template T
     * @param string|object   $target
     * @param string          $method
     * @param class-string<T> $class
     * @return ?T
     */
    public static function getMethodAttributeOf(string|object $target, string $method, string $class)
    {
        return @static::getMethodAttributesOf($target, $method, $class)[0];
    }


    /**
     * Get all method parameters attributes
     *
     * @param string|object $target
     * @param string        $method
     * @return array<string,mixed>
     */
    public static function getAllParameterAttributes(string|object $target, string $method)
    {
        if (is_object($target))
        {
            $target = get_class($target);
        }

        $lower = strtolower($method);
        if(isset(static::$_parameter_attributes[$target][$lower]))
        {
            return static::$_parameter_attributes[$target][$lower];
        }

        $all = [];
        foreach((new ReflectionMethod($target, $method))->getParameters() as $parameter)
        {
            $attrs = array_map(fn($attr) => $attr->newInstance(), $parameter->getAttributes());
            $all[$parameter->getName()] = $attrs;
        }

        return @static::$_parameter_attributes[$target][$lower] = $all;
    }

    /**
     * Get method parameter attributes
     *
     * @param string|object $target
     * @param string        $method
     * @param string        $parameter
     * @return array
     */
    public static function getParameterAttributes(string|object $target, string $method, string $parameter)
    {
        return static::getAllParameterAttributes($target, $method)[$parameter];
    }

    /**
     * Get method parameter attributes
     *
     * @template T
     * @param string|object   $target
     * @param string          $method
     * @param string          $parameter
     * @param class-string<T> $class
     * @return T[]
     */
    public static function getParameterAttributesOf(string|object $target, string $method, string $parameter, string $class)
    {
        return array_filter(static::getParameterAttributes($target, $method, $parameter), fn($attr) => $attr instanceof $class);
    }

    /**
     * Get method parameter attribute
     *
     * @template T
     * @param string|object   $target
     * @param string          $method
     * @param string          $parameter
     * @param class-string<T> $class
     * @return T
     */
    public static function getParameterAttributeOf(string|object $target, string $method, string $parameter, string $class)
    {
        return @static::getParameterAttributesOf($target, $method, $parameter, $class)[0];
    }

    /**
     * Normalize calling method
     *
     * @param string|object $target
     * @param string        $method
     * @param array         $args
     * @param Closure|null $callback
     * @return array
     */
    public static function getNormalizedCallingMethod(string|object $target, string $method, array $args, Closure $callback = null)
    {
        if (is_object($target))
        {
            $target = get_class($target);
        }

        $parametersAll = (new ReflectionMethod($target, $method))->getParameters();
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

        $notArgs = $args;
        $args = [];
        foreach($parameters as $parameter)
        {
            $value = array_key_exists($parameter->getName(), $notArgs) ?
                $notArgs[$parameter->getName()] :
                $parameter->getDefaultValue();

            $args[$parameter->getName()] = $value;
            unset($notArgs[$parameter->getName()]);

            if($instead = static::getParameterAttributeOf($target, $method, $parameter->getName(), ParameterPassingInstead::class))
            {
                $value = $instead->getInsteadOf($value);
            }

            if($callback)
            {
                $callback($parameter, $value);
            }
        }

        return [$args, $notArgs];
    }

}
