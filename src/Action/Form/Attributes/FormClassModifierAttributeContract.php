<?php

namespace Mmb\Action\Form\Attributes;

use Mmb\Action\Form\Form;

interface FormClassModifierAttributeContract
{

    public function registerFormClassModifier(Form $form);

}
