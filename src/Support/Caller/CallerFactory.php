<?php

namespace Mmb\Support\Caller;

use ArgumentCountError;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Mmb\Action\Action;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Section\Section;
use Mmb\Support\AttributeLoader\AttributeLoader;
use Mmb\Support\Caller\Attributes\CallingPassParameterInsteadContract;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

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
            // Invoke inline actions // TODO
            // if (!($normalArgs && $normalArgs[0] instanceof InlineAction) &&
            //     $class instanceof Section &&
            //     $inline = $class->tryCreateInlineFor($callable[1], ...$normalArgs + $dynamicArgs))
            // {
            //     return $inline->invoke();
            // }

            /** @var Action|string $class */
            $classAttrs = AttributeLoader::getClassAttributesOf($class, CallingClassAttribute::class);
            $methodAttrs = AttributeLoader::getMethodAttributesOf($class, $callable[1], CallingMethodAttribute::class);
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

    public function invokeAction(array|string|Action $callable, array $normalArgs, array $dynamicArgs = [])
    {
        // String callable -> invoke main() method
        if (is_string($callable))
        {
            return $this->invoke([new $callable, 'main'], $normalArgs, $dynamicArgs);
        }

        // Action callable -> invoke main() method
        if ($callable instanceof Action)
        {
            return $this->invoke([$callable, 'main'], $normalArgs, $dynamicArgs);
        }

        $count = count($callable);

        // Empty value -> error
        if ($count == 0)
        {
            throw new InvalidArgumentException("Callable is an empty array");
        }

        // Single value -> invoke main() method
        if ($count == 1)
        {
            if (is_string($callable[0]))
            {
                return $this->invoke([new $callable[0], 'main'], $normalArgs, $dynamicArgs);
            }
            else
            {
                return $this->invoke([$callable[0], 'main'], $normalArgs, $dynamicArgs);
            }
        }

        // More values -> invoke the second index method
        $action = $callable[0];
        if (is_string($action)) $action = new $action;

        return $this->invoke(
            [$action, $callable[1]],
            [...array_slice($callable, 2), ...$normalArgs],
            $dynamicArgs
        );
    }

    public function getParameterValue(
        ReflectionParameter|ReflectionProperty $parameter,
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
                if ($attribute instanceof CallingPassParameterInsteadContract)
                {
                    $value = $attribute->getPassParameterInstead($parameter, $value);
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
                if ($attribute instanceof CallingPassParameterInsteadContract)
                {
                    $value = $attribute->getPassParameterInstead($parameter, $value);
                }
            }
        }

        return $value;
    }

    // private function castParameter($value, string $class)
    // {
    //     return $value;
    // }

    /**
     * Split arguments to normalArgs & dynamicArgs
     *
     * @param array $args
     * @return array
     */
    public function splitArguments(array $args)
    {
        $dynamicArgs = [];
        foreach ($args as $key => $value)
        {
            if (is_string($key))
            {
                $dynamicArgs[$key] = $value;
                unset($args[$key]);
            }
        }

        return [$args, $dynamicArgs];
    }


    public function getGlobalInstanceOf(
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

    public function hasGlobalInstance(string $class)
    {
        return Container::getInstance()->bound($class);
    }

    public function getGlobalInstance(string $class)
    {
        return Container::getInstance()->make($class);
    }

}
