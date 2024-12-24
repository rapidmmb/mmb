<?php

namespace Mmb\Action\Section;

use Closure;
use Mmb\Action\Action;
use Mmb\Action\Memory\ConvertableToStep;
use Mmb\Action\Memory\StepFactory;
use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerArray as AsArray;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

class PipelineStepHandler extends StepHandler
{

    #[Alias('s')]
    #[Serialize]
    public array $steps = [];

    #[Alias('f')]
    #[SafeClass]
    public ?string $fallbackClass = null;

    #[Alias('F')]
    public ?string $fallbackMethod = null;


    public static function current(Context $context): ?PipelineStepHandler
    {
        if (($step = $context->stepFactory->get()) instanceof PipelineStepHandler) {
            return $step;
        }

        return null;
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

    public function keepFirst(Context $context)
    {
        $context->stepFactory->set($this->steps ? reset($this->steps) : null);
    }

    public function keepLast(Context $context)
    {
        $context->stepFactory->set($this->steps ? end($this->steps) : null);
    }

    public function listen(Context $context, Closure $callback)
    {
        $realFactory = $context->stepFactory;

        $context->stepFactory = new class($context, $this) extends StepFactory
        {
            public function __construct(Context $context, public PipelineStepHandler $pipe)
            {
                parent::__construct($context);
            }

            public function set(ConvertableToStep|StepHandler|null $step)
            {
                $this->pipe->push($step);
            }

            public function get()
            {
                return $this->pipe->steps ? end($this->pipe->steps) : null;
            }
        };

        $callback();

        $context->stepFactory = $realFactory;
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