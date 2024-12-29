<?php

namespace Mmb\Action\Form\Attributes;

use Attribute;
use Mmb\Action\Form\Exceptions\AttributeRequiredException;
use Mmb\Action\Form\Form;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required implements FormPropertyModifierAttributeContract
{

    public const NOT_NULL = 1;
    public const NOT_EMPTY = 2;

    public function __construct(
        public int $mode = self::NOT_NULL,
    )
    {
    }

    public function registerFormPropertyModifier(Form $form, string $property)
    {
        $form->requesting(function () use ($form, $property) {

            $value = $form->get($property);

            if (match ($this->mode) {
                self::NOT_NULL  => is_null($value),
                self::NOT_EMPTY => empty($value),
            }) {
                throw new AttributeRequiredException(
                    sprintf("Attribute [%s] is required in [%s]", $property, get_class($form)),
                );
            }

        });
    }

}