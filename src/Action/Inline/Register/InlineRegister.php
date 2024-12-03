<?php

namespace Mmb\Action\Inline\Register;

use Laravel\SerializableClosure\Support\ReflectionClosure;
use Mmb\Action\Action;
use Mmb\Action\Inline\Attributes\InlineAttributeContract;
use Mmb\Action\Inline\Attributes\InlineParameterAttributeContract;
use Mmb\Action\Inline\InlineAction;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

abstract class InlineRegister
{

    public InlineAction $inlineAction;

    public function __construct(
        public Context $context,
        string|InlineAction $inlineAction,

        public Action $target,
        public string $method,

        public ?\Closure $init = null,
        public array $callArgs = [],
        public array $haveItems = [],
    )
    {
        $this->inlineAction = is_string($inlineAction) ? new $inlineAction($this->context) : $inlineAction;
        $this->inlineAction->initializer($this->target, $this->method);
    }

    protected array $before = [];
    protected array $after = [];

    /**
     * Add listener before initializing
     *
     * @param $callback
     * @return $this
     */
    public function before($callback)
    {
        $this->before[] = $callback;
        return $this;
    }

    /**
     * Add listener after initializing
     *
     * @param $callback
     * @return $this
     */
    public function after($callback)
    {
        $this->after[] = $callback;
        return $this;
    }

    /**
     * Fire a listener
     *
     * @param array|string $listeners
     * @return void
     */
    protected function fire(array|string $listeners)
    {
        if (is_string($listeners))
        {
            $listeners = $this->$listeners;
        }

        foreach ($listeners as $listener)
        {
            $listener($this);
        }
    }

    /**
     * Register and return result action
     *
     * @return InlineAction
     */
    public abstract function register() : InlineAction;

    /**
     * Register attributes and parameters
     *
     * @return void
     */
    protected function registerAttributes()
    {
        $ref = new ReflectionClosure($this->init);

        foreach ($ref->getAttributes() as $attribute)
        {
            if (is_a($attribute->getName(), InlineAttributeContract::class, true))
            {
                /** @var InlineAttributeContract $attribute */
                $attribute = $attribute->newInstance();

                $attribute->registerInline($this);
            }
        }

        foreach ($ref->getParameters() as $index => $parameter)
        {
            if ($index > 0)
            {
                $this->onRegisterParameter($parameter->name);

                foreach ($parameter->getAttributes() as $attribute)
                {
                    if (is_a($attribute->getName(), InlineParameterAttributeContract::class, true))
                    {
                        /** @var InlineParameterAttributeContract $attribute */
                        $attribute = $attribute->newInstance();

                        $attribute->registerInlineParameter($this, $parameter->name);
                    }
                }

                $this->onRegisteredParameter($parameter->name);
            }
        }
    }

    /**
     * Event for registering parameters
     *
     * @param string $name
     * @return void
     */
    protected function onRegisterParameter(string $name)
    {
    }

    /**
     * Event for registered parameters
     *
     * @param string $name
     * @return void
     */
    protected function onRegisteredParameter(string $name)
    {
    }

    /**
     * Register inline action boot
     *
     * @return void
     */
    protected function registerBoot()
    {
    }

    /**
     * Register inline action by calling target method
     *
     * @return void
     */
    protected function registerByCall()
    {
        Caller::invoke(
            $this->init,
            [$this->inlineAction, ...$this->callArgs]
        );
    }

    /**
     * Register inline action with haveItems
     *
     * @return void
     */
    protected function registerHaveItems()
    {
    }


    /**
     * Add item to store in memory (if supported by register)
     *
     * @param string $name
     * @param        $data
     * @return $this
     */
    public function shouldHave(string $name, $data)
    {
        $this->haveItems[$name] = $data;
        return $this;
    }

    /**
     * Remove item from storing
     *
     * @param string $name
     * @return $this
     */
    public function dontHave(string $name)
    {
        unset($this->haveItems[$name]);
        return $this;
    }

    /**
     * Get last item storing value (or call-arg if failed)
     *
     * @param string $name
     * @return mixed
     */
    public function getHaveItem(string $name)
    {
        return array_key_exists($name, $this->haveItems) ?
            $this->haveItems[$name] :
            @$this->callArgs[$name];
    }

}
