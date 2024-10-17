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
use Mmb\Exceptions\AbortException;
use Mmb\Support\Caller\Attributes\CallingPassParameterInsteadContract;
use Mmb\Support\Db\ModelFinder;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindDynamic implements
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


    protected bool  $allowNull;
    protected array $classType;

    /**
     * Try to find class type
     *
     * @param ReflectionType $type
     * @return array
     */
    public function setClassTypeUsing(ReflectionType $type)
    {
        $this->allowNull = $type->allowsNull();

        if ($type instanceof \ReflectionUnionType)
            $types = $type->getTypes();
        else
            $types = [$type];

        $result = [];
        foreach ($types as $type)
        {
            if ($type instanceof \ReflectionNamedType)
            {
                if (!$type->isBuiltin() && is_a($type->getName(), Model::class, true))
                {
                    $result[] = $type->getName();
                }
            }
            elseif ($type instanceof \ReflectionIntersectionType)
            {
                throw new \TypeError(sprintf("Attribute [%s] can't parse intersection types", static::class));
            }
        }

        if (!$result)
        {
            throw new \TypeError(sprintf("Attribute [%s] required a model type", static::class));
        }

        return $this->classType = $result;
    }

    /**
     * Get storable value
     *
     * @param $value
     * @return mixed
     */
    protected function getStorableValue($value)
    {
        if ($value instanceof Model)
        {
            $keyValue = isset($this->by) ? $value->getAttribute($this->by) : $value->getKey();

            if ($keyValue === null)
            {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Failed to store [%s] by [%s], because it's null",
                        get_class($value),
                        $this->by ?? $value->getKeyName()
                    )
                );
            }

            return ModelFinder::storeDynamic($value, $this->by);
        }

        return $value;
    }

    /**
     * Get usable value
     *
     * @param $value
     * @return Model|mixed
     */
    protected function getUsableValue($value)
    {
        if ($value instanceof Model)
        {
            return $value;
        }

        if ($value === null)
        {
            if ($this->allowNull)
            {
                return null;
            }

            throw new AbortException($this->error ?? 404, $this->failMessage);
        }

        return ModelFinder::findDynamic(
            $this->classType,
            $value,
            by         : $this->by,
            withTrashed: $this->withTrashed,
            orFail     : $this->error ?? ($this->nullOnFail ? null : true),
            failMessage: $this->failMessage,
        );
    }


    public function registerInlineParameter(InlineRegister $register, string $name)
    {
        $this->setClassTypeUsing(
            (new \ReflectionParameter($register->init, $name))->getType()
        );

        if ($register instanceof InlineCreateRegister)
        {
            $register->before(
                fn () => $register->shouldHave($name, $this->getStorableValue($register->getHaveItem($name))),
            );
        }
        elseif ($register instanceof InlineLoadRegister)
        {
            $register->before(
                fn () => $register->callArgs[$name] = $this->getUsableValue($register->callArgs[$name]),
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
