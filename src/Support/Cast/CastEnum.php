<?php

namespace Mmb\Support\Cast;

use Attribute;
use Mmb\Action\Form\Attributes\FormDynamicPropertyAttributeContract;
use Mmb\Action\Form\Form;
use Mmb\Action\Inline\Attributes\InlineParameterAttributeContract;
use Mmb\Action\Inline\Attributes\InlineWithPropertyAttributeContract;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineCreateRegister;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Road\Attributes\StationParameterResolverAttributeContract;
use Mmb\Action\Road\Attributes\StationPropertyResolverAttributeContract;
use Mmb\Support\Caller\Attributes\CallingPassParameterInsteadContract;
use ReflectionParameter;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class CastEnum implements
    InlineParameterAttributeContract,
    InlineWithPropertyAttributeContract,
    FormDynamicPropertyAttributeContract,
    CallingPassParameterInsteadContract,
    StationParameterResolverAttributeContract,
    StationPropertyResolverAttributeContract
{

    private bool $allowNull;
    private string $classType;

    /**
     * Try to find class type
     *
     * @param \ReflectionType $type
     * @return string
     */
    public function setClassTypeUsing(\ReflectionType $type)
    {
        $this->allowNull = $type->allowsNull();

        if ($type instanceof \ReflectionUnionType)
            $types = $type->getTypes();
        else
            $types = [$type];

        foreach ($types as $type)
        {
            if ($type instanceof \ReflectionNamedType)
            {
                if (!$type->isBuiltin() && enum_exists($type->getName()))
                {
                    return $this->classType = $type->getName();
                }
            }
            elseif ($type instanceof \ReflectionIntersectionType)
            {
                throw new \TypeError(sprintf("Attribute [%s] can't parse intersection types", static::class));
            }
        }

        throw new \TypeError(sprintf("Attribute [%s] required a enum type", static::class));
    }

    /**
     * Get storable value
     *
     * @param $value
     * @return mixed
     */
    protected function getStorableValue($value)
    {
        if ($value instanceof \BackedEnum)
        {
            return $value->value;
        }
        elseif ($value instanceof \UnitEnum)
        {
            return $value->name;
        }

        return $value;
    }

    /**
     * Get usable value
     *
     * @param $value
     * @return \UnitEnum|\BackedEnum|mixed
     */
    protected function getUsableValue($value)
    {
        if (!is_object($value) && !is_null($value))
        {
            if (is_a($this->classType, \BackedEnum::class, true))
            {
                return $this->classType::tryFrom($value);
            }
            elseif (is_a($this->classType, \UnitEnum::class, true))
            {
                foreach($this->classType::cases() as $case)
                {
                    if($case->name == $value)
                    {
                        return $case;
                    }
                }

                return null;
            }
        }

        return $value;
    }


    public function registerInlineParameter(InlineRegister $register, string $name)
    {
        $this->setClassTypeUsing(
            (new \ReflectionParameter($register->init, $name))->getType()
        );

        if ($register instanceof InlineCreateRegister)
        {
            $register->before(
                fn() => $register->shouldHave($name, $this->getStorableValue($register->getHaveItem($name))),
            );
        }
        elseif ($register instanceof InlineLoadRegister)
        {
            $register->before(
                fn() => $register->callArgs[$name] = $this->getUsableValue($register->callArgs[$name]),
            );
        }
    }

    public function getInlineWithPropertyForStore(InlineAction $inline, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($inline->getInitializer()[0], $name))->getType()
        );

        return $this->getStorableValue($value);
    }

    public function getInlineWithPropertyForLoad(InlineAction $inline, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($inline->getInitializer()[0], $name))->getType()
        );

        return $this->getUsableValue($value);
    }

    public function getFormDynamicPropertyForStore(Form $form, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($form, $name))->getType()
        );

        return $this->getStorableValue($value);
    }

    public function getFormDynamicPropertyForLoad(Form $form, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($form, $name))->getType()
        );

        return $this->getUsableValue($value);
    }

    public function getPassParameterInstead(ReflectionParameter $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getUsableValue($value);
    }

    public function getStationParameterForStore(ReflectionParameter $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getStorableValue($value);
    }

    public function getStationParameterForLoad(ReflectionParameter $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getUsableValue($value);
    }

    public function getStationPropertyForStore(ReflectionProperty $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getStorableValue($value);
    }

    public function getStationPropertyForLoad(ReflectionProperty $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getUsableValue($value);
    }
}
