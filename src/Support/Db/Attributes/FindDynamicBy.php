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
use Mmb\Support\Caller\Attributes\CallingPassParameterInsteadContract;
use Mmb\Support\Db\ModelFinder;
use ReflectionParameter;
use ReflectionType;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindDynamicBy implements
    InlineParameterAttributeContract,
    InlineWithPropertyAttributeContract,
    FormDynamicPropertyAttributeContract,
    CallingPassParameterInsteadContract
{

    public function __construct(
        public string $key,
        public ?int $error = 404,
    )
    {
    }


    protected bool $allowNull;
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
            ModelFinder::store($value);

            $key = $this->key ? $value->getAttribute($this->key) : $value->getKey();

            if ($key === null)
            {
                throw new \InvalidArgumentException(sprintf("Failed to store [%s] by [%s], because it's null", get_class($value), $this->key ?: $value->getKeyName()));
            }

            return class_basename($value) . ':' . $key;
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
        if (is_string($value))
        {
            if($this->error)
            {
                return ModelFinder::findDynamicBy($this->classType, $this->key, $value, fn() => abort($this->error));
            }
            else
            {
                return ModelFinder::findDynamicBy($this->classType, $this->key, $value);
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
}
