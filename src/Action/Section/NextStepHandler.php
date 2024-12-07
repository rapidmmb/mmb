<?php

namespace Mmb\Action\Section;

use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerShortClass as ShortClass;
use Mmb\Action\Memory\StepHandler;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class NextStepHandler extends StepHandler
{

    #[Alias('c')]
    #[ShortClass('App\\Mmb\\Sections\\', '*')]
    #[SafeClass('')]
    public string $action;

    #[Alias('m')]
    public string $method;

    public function for(string|array $action, string $method = null)
    {
        if(is_array($action))
        {
            [$this->action, $this->method] = $action;
        }
        else
        {
            $this->action = $action;
            $this->method = $method;
        }

        return $this;
    }

    public function handle(Context $context, Update $update) : void
    {
        if (class_exists($this->action) && method_exists($this->action, 'make'))
        {
            $action = $this->action::makeByContext($context);
            $action->invoke($this->method);
            return;
        }

        $update->skipHandler();
    }

}
