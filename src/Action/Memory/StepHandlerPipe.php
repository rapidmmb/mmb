<?php

namespace Mmb\Action\Memory;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\Contracts\Stepper;

class StepHandlerPipe implements UpdateHandling
{

    public function __construct(
        public Stepper $stepper,
    )
    {
    }

    public function handleUpdate(Context $context, Update $update)
    {
        if ($step = $this->stepper->getStep()) {
            $step->handle($context, $update);
            return;
        }

        $update->skipHandler();
    }

}
