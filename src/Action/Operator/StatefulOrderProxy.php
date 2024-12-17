<?php

namespace Mmb\Action\Operator;

use Closure;

/**
 * @template T
 */
class StatefulOrderProxy
{

    public function __construct(
        protected OperatorService $operator,
    )
    {
    }

    public function __get(string $name)
    {
        return $this->operator->$name;
    }

    public function __set(string $name, $value): void
    {
        $this->operator->$name = $value;
    }

    public function __call(string $name, array $arguments)
    {
        try {

            $result = $this->operator->$name(...$arguments);

        } catch (OperatorFailed $failed) {

            if ($this->fail) {
                return ($this->fail)($failed);
            }

            throw $failed;

        } catch (\Throwable $exception) {

            if ($this->catch === false) {
                return null;
            }

            if ($this->catch) {
                return ($this->catch)($exception);
            }

            throw $exception;

        }

        if ($result === $this->operator) {
            return $this;
        }

        if ($this->then) {
            return ($this->then)($result);
        }

        return $result;
    }


    protected Closure|false|null $catch = null;

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

    protected Closure|null $fail = null;

    /**
     * Set on fail handler callback
     *
     * @param Closure $callback
     * @return $this|T
     */
    public function fail(Closure $callback)
    {
        $this->fail = $callback;
        return $this;
    }

    protected Closure|null $then = null;

    /**
     * Set on success handler callback
     *
     * @param Closure $callback
     * @return $this|T
     */
    public function then(Closure $callback)
    {
        $this->then = $callback;
        return $this;
    }

    /**
     * Get stateless operator service
     *
     * @return T
     */
    public function stateless(): OperatorService
    {
        return $this->operator;
    }

}