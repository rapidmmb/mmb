<?php

namespace Mmb\Action\Form\Attributes;

use Mmb\Action\Form\Form;

interface FormDynamicPropertyAttributeContract
{

    public function getFormDynamicPropertyForStore(Form $form, string $name, $value);

    public function getFormDynamicPropertyForLoad(Form $form, string $name, $value);

}
