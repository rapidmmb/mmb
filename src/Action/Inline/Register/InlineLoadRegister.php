<?php

namespace Mmb\Action\Inline\Register;

use Laravel\SerializableClosure\Support\ReflectionClosure;
use Mmb\Action\Action;
use Mmb\Action\Inline\Attributes\InlineAttribute;
use Mmb\Action\Inline\InlineAction;
use Mmb\Support\Caller\Caller;
use Symfony\Component\Routing\Alias;

class InlineLoadRegister extends InlineRegister
{

    public function register() : InlineAction
    {
        $this->registerAttributes();

        $this->fire('before');

        $this->registerBoot();
        // $this->registerHaveItems();
        $this->registerByCall();

        $this->fire('after');

        return $this->inlineAction;
    }

    protected function onRegisterParameter(string $name)
    {
        $this->callArgs[$name] = $this->inlineAction->get($name);
    }

}
