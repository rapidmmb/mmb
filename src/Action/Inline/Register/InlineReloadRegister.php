<?php

namespace Mmb\Action\Inline\Register;

use Mmb\Action\Inline\InlineAction;

class InlineReloadRegister extends InlineCreateRegister
{

    public InlineAction $from;

    public function from(InlineAction $from)
    {
        $this->from = $from;
        return $this;
    }

    protected function onRegisterParameter(string $name)
    {
        $this->shouldHave($name, $this->from->get($name));
    }

}
