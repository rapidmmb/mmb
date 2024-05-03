<?php

namespace Mmb\Support\Caller;

use ArgumentCountError;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Mmb\Action\Action;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

class CallerFactory
{

    public function invoke($callable, array $normalArgs, array $dynamicArgs = [])
    {
        $func = new ReflectionFunction(
            $callable instanceof Closure || is_string($callable) ?
                $callable :
                $callable(...),
        );

        // Load class attributes
        if(is_array($callable) && count($callable) == 2 && is_a($class = $callable[0], Action::class, true))
        {
            /** @var Action|string $class */
            $classAttrs = $class::getClassAttributesOf(CallingClassAttribute::class);
            $methodAttrs = $class::getMethodAttributesOf($callable[1], CallingMethodAttribute::class);
        }
        else
        {
            $classAttrs = [];
            $methodAttrs = [];
        }

        foreach($classAttrs as $attr) $attr->authorize();
        foreach($methodAttrs as $attr) $attr->authorize();

        $params = [];
        foreach($func->getParameters() as $parameter)
        {
            $name = $parameter->getName();
            if($parameter->isVariadic())
            {
                foreach($normalArgs as $name => $arg)
                {
                    if(is_string($name)) $params[$name] = $arg;
                    else $params[] = $arg;
                }
                $normalArgs = null;
                break;
            }
            elseif(array_key_exists($name, $normalArgs))
            {
                $params[] = $this->getParameterValue($parameter, $normalArgs[$name]);
                unset($normalArgs[$name]);
            }
            elseif(is_int($key = array_key_first($normalArgs)))
            {
                $params[] = $this->getParameterValue($parameter, $normalArgs[$key]);
                unset($normalArgs[$key]);
            }
            elseif(array_key_exists($name, $dynamicArgs))
            {
                $params[] = $this->getParameterValue($parameter, value($dynamicArgs[$name]));
            }
            elseif($parameter->isOptional())
            {
                foreach($normalArgs as $name => $arg)
                {
                    if(is_string($name)) $params[$name] = $arg;
                    else $params[] = $arg;
                }
                $normalArgs = null;
                break;
            }
            elseif($this->getGlobalInstanceOf($parameter, $value))
            {
                $params[] = $value;
            }
            else
            {
                throw new ArgumentCountError("Too few arguments to function ".$func->getName()."(), argument \$$name is not passed");
            }
        }

        if($normalArgs && !($callable instanceof Closure))
        {
            throw new ArgumentCountError("Too many arguments to function ".$func->getName()."() passed, required " . $func->getNumberOfParameters());
        }

        return $func->invokeArgs($params);
    }

    private function getParameterValue(
        ReflectionParameter $parameter,
        $value
    )
    {
        $type = $parameter->getType();
        if($type instanceof ReflectionNamedType && !$type->isBuiltin())
        {
            $type = $type->getName();

            foreach($parameter->getAttributes() as $attribute)
            {
                $attribute = $attribute->newInstance();
                if($attribute instanceof CallingParameterAttribute)
                {
                    $attribute->authorize($value);
                    $value = $attribute->cast($value, $type);
                }
            }
        }
        elseif($type instanceof \ReflectionIntersectionType)
        {
            $types = [];
            foreach($type->getTypes() as $t)
            {
                if(!$t->isBuiltin())
                {
                    $types[] = $t->getName();
                }
            }

            foreach($parameter->getAttributes() as $attribute)
            {
                $attribute = $attribute->newInstance();
                if($attribute instanceof CallingParameterAttribute)
                {
                    $attribute->authorize($value);
                    $value = $attribute->castMultiple($value, $types);
                }
            }
        }

        return $value;
    }

    // private function castParameter($value, string $class)
    // {
    //     return $value;
    // }


    private function getGlobalInstanceOf(
        ReflectionParameter $parameter,
        &$value
    )
    {
        $type = $parameter->getType();
        if($type instanceof ReflectionNamedType && !$type->isBuiltin())
        {
            $class = $type->getName();
            if($this->hasGlobalInstance($class))
            {
                $value = $this->getGlobalInstance($class);
                return true;
            }
        }

        return false;
    }

    private function hasGlobalInstance(string $class)
    {
        return Container::getInstance()->bound($class);
    }

    private function getGlobalInstance(string $class)
    {
        return Container::getInstance()->make($class);
    }

}
