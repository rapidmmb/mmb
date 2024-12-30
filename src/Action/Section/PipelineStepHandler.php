<?php

namespace Mmb\Action\Section;

use Closure;
use Mmb\Action\Action;
use Mmb\Action\Memory\ConvertableToStep;
use Mmb\Action\Memory\Factories\PipelineFactory;
use Mmb\Action\Memory\StepCollectionHandler;
use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerArray as AsArray;
use Mmb\Action\Memory\StepListenerFactory;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

class PipelineStepHandler extends StepCollectionHandler
{

    #[Alias('s')]
    #[Serialize]
    public array $steps = [];

    #[Alias('f')]
    #[SafeClass]
    public ?string $fallbackClass = null;

    #[Alias('F')]
    public ?string $fallbackMethod = null;

    public function add(StepHandler $step)
    {
        $this->push($step);
    }

    public function push(StepHandler $handler)
    {
        $this->steps[] = $handler;
    }

    public function pushCurrent(Context $context)
    {
        if ($current = $context->stepFactory->get()) {
            $this->push($current);
        }
    }

    public function listen(Context $context, Closure $callback): void
    {
        StepListenerFactory::listen(
            $context,
            $callback,
            function (StepHandler|ConvertableToStep|null $step) {
                if ($step instanceof ConvertableToStep) {
                    $step = $step->toStep();
                }

                if ($step) {
                    $this->push($step);
                }
            },
        );
    }

    public function toFactory(Context $context): PipelineFactory
    {
        return new PipelineFactory($context, $this);
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

        $update->skipHandler();
    }

}