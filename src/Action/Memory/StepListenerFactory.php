<?php

namespace Mmb\Action\Memory;

use Closure;
use Mmb\Context;

class StepListenerFactory extends StepFactory
{

    protected StepFactory $base;

    public function __construct(
        Context            $context,
        protected ?Closure $get = null,
        protected ?Closure $set = null,
        protected ?Closure $setting = null,
    )
    {
        parent::__construct($context);

        $this->setModel($context->stepFactory->getModel());
        $this->set($context->stepFactory->get());
    }

    protected ?StepHandler $step = null;

    public function get()
    {
        if ($this->get) {
            return ($this->get)();
        }

        return $this->step;
    }

    public function set(ConvertableToStep|StepHandler|null $step)
    {
        if ($this->setting) {
            ($this->setting)($step);
        }

        if ($this->set) {
            ($this->set)($step);
            return;
        }

        $this->step = $step instanceof ConvertableToStep ? $step->toStep() : $step;
    }


    public static function replace(
        Context  $context,
        Closure  $callback,
        ?Closure $get = null,
        ?Closure $set = null,
    ): void
    {
        $listener = new static($context, get: $get, set: $set);

        $actual = $context->stepFactory;
        $context->stepFactory = $listener;

        $callback();

        $context->stepFactory = $actual;
    }

    public static function listen(
        Context $context,
        Closure $callback,
        Closure $setting,
    ): void
    {
        $listener = new static($context, setting: $setting);

        $actual = $context->stepFactory;
        $context->stepFactory = $listener;

        $callback();

        $context->stepFactory = $actual;
    }

}