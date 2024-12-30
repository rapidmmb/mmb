<?php

namespace Mmb\Action\Operator;

use Closure;
use Mmb\Context;

/**
 * @template T
 */
class EventTriggerProxy
{

    public function __construct(
        protected string  $_action,
        protected Context $context,
    )
    {
    }

    public function __call(string $name, array $arguments)
    {
        try {

            return $this->_action::makeByContext($this->context)->$name(...$arguments);

        } catch (\Throwable $exception) {

            if ($this->catch) {
                return ($this->catch)($exception);
            }

            if ($this->catch === false) {
                return null;
            }

            throw $exception;

        }
    }

    /**
     * Set the context
     *
     * @param Context $context
     * @return $this|T
     */
    public function withContext(Context $context)
    {
        $this->context = $context;
        return $this;
    }

    protected Closure|false|null $catch = null;

    /**
     * Ignore errors
     *
     * @return $this|T
     */
    public function ignore()
    {
        $this->catch = false;
        return $this;
    }

    /**
     * Set a handler to catch the errors.
     * Pass no parameters to ignore the errors.
     *
     * @param Closure|null $callback
     * @return $this|T
     */
    public function catch(Closure|null $callback = null)
    {
        $this->catch = $callback ?? false;
        return $this;
    }

}