<?php

namespace Mmb\Action\Form\Attributes;

use Mmb\Action\Form\Form;
use Mmb\Context;

interface FormDynamicPropertyAttributeContract
{

    public function getFormDynamicPropertyForStore(Context $context, Form $form, string $name, $value);

    public function getFormDynamicPropertyForLoad(Context $context, Form $form, string $name, $value);

}
