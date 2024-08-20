<?php

namespace Mmb\Action\Inline\Register;

use Mmb\Action\Inline\InlineAction;

class InlineReloadRegister extends InlineLoadRegister
{

    public InlineAction $from;

    public function from(InlineAction $from)
    {
        $this->from = $from;
        return $this;
    }

    protected function onRegisterParameter(string $name)
    {
        $value = $this->from->get($name);

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
