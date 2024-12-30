<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;
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
use Mmb\Context;
use Mmb\Exceptions\AbortException;
use Mmb\Support\Caller\Attributes\CallingPassParameterInsteadContract;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Find implements
    InlineParameterAttributeContract,
    InlineWithPropertyAttributeContract,
    FormDynamicPropertyAttributeContract,
    CallingPassParameterInsteadContract,
    StationParameterResolverAttributeContract,
    StationPropertyResolverAttributeContract
{

    public function __construct(
        public ?string $by = null,
        public ?int    $error = null,
        public mixed   $failMessage = null,
        public bool    $nullOnFail = false,
        public bool    $withTrashed = false,
    )
    {
    }


    protected bool $allowNull;
    protected string $classType;

    /**
     * Try to find class type
     *
     * @param ReflectionType $type
     * @return string
     */
    public function setClassTypeUsing(ReflectionType $type)
    {
        $this->allowNull = $type->allowsNull();

        if ($type instanceof \ReflectionUnionType)
            $types = $type->getTypes();
        else
            $types = [$type];

        foreach ($types as $type) {
            if ($type instanceof \ReflectionNamedType) {
                if (!$type->isBuiltin() && is_a($type->getName(), Model::class, true)) {
                    return $this->classType = $type->getName();
                }
            } elseif ($type instanceof \ReflectionIntersectionType) {
                throw new \TypeError(sprintf("Attribute [%s] can't parse intersection types", static::class));
            }
        }

        throw new \TypeError(sprintf("Attribute [%s] required a model type", static::class));
    }

    /**
     * Get storable value
     *
     * @param Context $context
     * @param $value
     * @return mixed
     */
    protected function getStorableValue(Context $context, $value)
    {
        if ($value instanceof Model) {
            $context->finder->store($value);

            $key = isset($this->by) ? $value->getAttribute($this->by) : $value->getKey();

            if ($key === null) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Failed to store [%s] by [%s], because it's null",
                        get_class($value),
                        $this->by ?? $value->getKeyName()
                    )
                );
            }

            return $key;
        }

        return $value;
    }

    /**
     * Get usable value
     *
     * @param Context $context
     * @param $value
     * @return Model|mixed
     */
    protected function getUsableValue(Context $context, $value)
    {
        if ($value instanceof Model) {
            return $value;
        }

        if ($value === null) {
            if ($this->allowNull) {
                return null;
            }

            throw new AbortException($this->error ?? 404, $this->failMessage);
        }

        return $context->finder->find(
            $this->classType,
            $value,
            by: $this->by,
            withTrashed: $this->withTrashed,
            orFail: $this->error ?? ($this->nullOnFail ? null : true),
            failMessage: $this->failMessage,
        );
    }


    public function registerInlineParameter(Context $context, InlineRegister $register, string $name)
    {
        $this->setClassTypeUsing(
            (new \ReflectionParameter($register->init, $name))->getType()
        );

        if ($register instanceof InlineCreateRegister) {
            $register->before(
                fn() => $register->shouldHave($name, $this->getStorableValue($context, $register->getHaveItem($name))),
            );
        } elseif ($register instanceof InlineLoadRegister) {
            $register->before(
                fn() => $register->callArgs[$name] = $this->getUsableValue($context, $register->callArgs[$name]),
            );
        }
    }

    public function getInlineWithPropertyForStore(Context $context, InlineAction $inline, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($inline->getInitializer()[0], $name))->getType()
        );

        return $this->getStorableValue($context, $value);
    }

    public function getInlineWithPropertyForLoad(Context $context, InlineAction $inline, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($inline->getInitializer()[0], $name))->getType()
        );

        return $this->getUsableValue($context, $value);
    }

    public function getFormDynamicPropertyForStore(Context $context, Form $form, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($form, $name))->getType()
        );

        return $this->getStorableValue($context, $value);
    }

    public function getFormDynamicPropertyForLoad(Context $context, Form $form, string $name, $value)
    {
        $this->setClassTypeUsing(
            (new \ReflectionProperty($form, $name))->getType()
        );

        return $this->getUsableValue($context, $value);
    }

    public function getPassParameterInstead(Context $context, ReflectionParameter $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getUsableValue($context, $value);
    }

    public function getStationParameterForStore(Context $context, ReflectionParameter $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getStorableValue($context, $value);
    }

    public function getStationParameterForLoad(Context $context, ReflectionParameter $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getUsableValue($context, $value);
    }

    public function getStationPropertyForStore(Context $context, ReflectionProperty $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getStorableValue($context, $value);
    }

    public function getStationPropertyForLoad(Context $context, ReflectionProperty $parameter, $value)
    {
        $this->setClassTypeUsing(
            $parameter->getType()
        );

        return $this->getUsableValue($context, $value);
    }
}
