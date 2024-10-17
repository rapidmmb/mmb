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
        $this->registerHaveItems();
        $this->registerByCall();

        $this->fire('after');

        return $this->inlineAction;
    }

    protected function onRegisterParameter(string $name)
    {
        $value = $this->inlineAction->get($name);

        $this->callArgs[$name] = $value;
        $this->haveItems[$name] = $value;
    }

    /**
     * Register inline action with haveItems
     *
     * @return void
     */
    protected function registerHaveItems()
    {
        foreach ($this->haveItems as $name => $item)
        {
            $this->inlineAction->have($name, $item, $item);
            unset($item);
        }
    }

}
