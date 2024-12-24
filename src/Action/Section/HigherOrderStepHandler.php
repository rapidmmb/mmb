<?php

namespace Mmb\Action\Section;

use Mmb\Action\Action;
use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerShortClass as ShortClass;
use Mmb\Action\Memory\Attributes\StepHandlerArray as AsArray;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

class HigherOrderStepHandler extends StepHandler
{

    #[Alias('s')]
    #[Serialize]
    #[AsArray]
    public array $steps = [];

    #[Alias('f')]
    #[SafeClass]
    public ?string $fallbackClass = null;

    #[Alias('F')]
    public ?string $fallbackMethod = null;


    public function push(StepHandler $handler)
    {
        $this->steps[] = $handler;
    }

    public function handle(Context $context, Update $update): void
    {
        foreach ($this->steps as $step) {
            if ($step instanceof StepHandler) {
                $update->isHandled = true;

                $step->handle($context, $update);

                if ($update->isHandled) {
                    return;
                }
            }
        }

        if (isset($this->fallbackClass) && is_a($this->fallbackClass, Action::class, true)) {
            $update->isHandled = true;

            Caller::invokeAction($context, [$this->fallbackClass, $this->fallbackMethod], []);

            return;
        }

        $update->isHandled = false;
    }

}