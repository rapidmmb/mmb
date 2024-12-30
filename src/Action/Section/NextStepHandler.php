<?php

namespace Mmb\Action\Section;

use Mmb\Action\Action;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerShortClass as ShortClass;
use Mmb\Action\Memory\Attributes\StepHandlerArray as AsArray;
use Mmb\Action\Memory\StepHandler;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

class NextStepHandler extends StepHandler
{

    /**
     * todo
     *
     * Add new inline action named NextStep or anything...
     */

    #[Alias('c')]
    #[ShortClass('App\\Mmb\\Sections\\', '*')]
    #[SafeClass('')]
    public string $action;

    #[Alias('m')]
    public string $method;

    #[Alias('a')]
    #[Serialize]
    public ?array $arguments = null;

    public function for(string|array $action, string $method = null, array $arguments = [])
    {
        if (is_array($action)) {
            [$this->action, $this->method] = $action;
        } else {
            $this->action = $action;
            $this->method = $method;
        }

        $this->arguments = $arguments ?: null;

        return $this;
    }

    public function handle(Context $context, Update $update): void
    {
        if (class_exists($this->action) && is_a($this->action, Action::class, true)) {
            Caller::invokeAction($context, [$this->action, $this->method], $this->arguments ?? []);
            return;
        }

        $update->skipHandler();
    }

}
