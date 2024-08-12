<?php

namespace Mmb\Action\Form\Attributes;

use Attribute;
use Mmb\Action\Form\Form;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AsAttribute implements FormPropertyModifierAttributeContract
{

    public function registerFormPropertyModifier(Form $form, string $property)
    {
        $form->mergeDynamicAttributes([$property]);
    }

}
