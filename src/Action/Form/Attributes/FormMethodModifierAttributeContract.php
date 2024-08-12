<?php

namespace Mmb\Action\Form\Attributes;

use Mmb\Action\Form\Form;

interface FormMethodModifierAttributeContract
{

    public function registerFormMethodModifier(Form $form, string $method);

}
