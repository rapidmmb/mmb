<?php

namespace Mmb\Action\Memory;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\Stepping;

class StepHandlerPipe implements UpdateHandling
{

    public function __construct(
        public Stepping $stepping,
    )
    {
    }

    public function handleUpdate(Update $update)
    {
        if ($step = $this->stepping->getStep())
        {
            $step->handle($update);
            return; // TODO
        }

        $update->skipHandler();
    }

}
