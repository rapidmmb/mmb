<?php

namespace Mmb\Action\Inline;

use Mmb\Action\Action;

/**
 * @template T of InlineAction
 * @mixin T
 */
class DefferInlineProxy
{

    public function __construct(
        protected Action              $__action,
        protected string|InlineAction $__inlineAction,
        protected string              $__method,
    )
    {
    }

    /**
     * @param ...$args
     * @return T
     */
    public function make(...$args)
    {
        return $this->__action->createInlineRegister($this->__inlineAction, $this->__method, $args)->register();
    }

    public function __call(string $name, array $arguments)
    {
        return $this->make()->$name(...$arguments);
    }

    public function __get(string $name)
    {
        return $this->make()->$name;
    }

}