<?php

namespace Mmb\Action\Memory;

use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Context;

abstract class StepCollectionHandler extends StepHandler
{

    #[Alias('s')]
    #[Serialize]
    public array $steps = [];

    abstract public function add(StepHandler $step);

    public function addCurrent(Context $context)
    {
        if ($step = $context->stepFactory->get()) {
            $this->add($step);
        }
    }


    public function fire(string $event, ...$args)
    {
        foreach ($this->steps as $step) {
            if ($step instanceof StepHandler) {
                $step->fire($event, ...$args);
            }
        }

        return parent::fire($event, ...$args);
    }

}