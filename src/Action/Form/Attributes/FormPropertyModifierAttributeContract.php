<?php

namespace Mmb\Action\Form\Attributes;

use Mmb\Action\Form\Form;

interface FormPropertyModifierAttributeContract
{

    public function registerFormPropertyModifier(Form $form, string $property);

}
