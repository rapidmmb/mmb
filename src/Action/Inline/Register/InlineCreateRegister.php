<?php

namespace Mmb\Action\Inline\Register;

use Mmb\Action\Inline\InlineAction;

class InlineCreateRegister extends InlineRegister
{

    public function register() : InlineAction
    {
        $this->registerAttributes();

        $this->fire('before');

        $this->registerBoot();
        $this->registerHaveItems();
        $this->registerByCall();

        $this->fire('after');

        return $this->inlineAction;
    }

    protected function onRegisterParameter(string $name)
    {
        $this->shouldHave($name, $this->callArgs[$name] ?? null);
    }

}
