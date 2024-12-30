<?php

namespace Mmb\Action\Memory\Factories;

use Closure;
use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Section\PipelineStepHandler;
use Mmb\Context;

class PipelineFactory
{

    public function __construct(
        public readonly Context             $context,
        public readonly PipelineStepHandler $handler,
    )
    {
    }

    public function pushCurrent()
    {
        $this->handler->pushCurrent($this->context);
        return $this;
    }

    public function push(StepHandler|Closure ...$handlers)
    {
        foreach ($handlers as $handler) {
            if ($handler instanceof Closure) {
                $this->handler->listen($this->context, $handler);
            } else {
                $this->handler->push($handler);
            }
        }
        return $this;
    }

    public function at(int $index): ?StepHandler
    {
        return @$this->handler->steps[$index];
    }

    public function first(): ?StepHandler
    {
        return $this->handler->steps ? head($this->handler->steps) : null;
    }

    public function last(): ?StepHandler
    {
        return $this->handler->steps ? end($this->handler->steps) : null;
    }

    public function all(): array
    {
        return $this->handler->steps;
    }

    public function keep()
    {
        $this->handler->keep($this->context);
    }

    public function flatten()
    {
        $steps = [];
        foreach ($this->handler->steps as $step) {
            if ($step instanceof PipelineStepHandler) {
                array_push($steps, ...$step->steps);
            } else {
                $steps[] = $step;
            }
        }

        $this->handler->steps = $steps;
        return $this;
    }

    public function removeRecursive()
    {
        $steps = [];
        foreach ($this->handler->steps as $step) {
            if (!($step instanceof PipelineStepHandler)) {
                $steps[] = $step;
            }
        }

        $this->handler->steps = $steps;
        return $this;
    }

}