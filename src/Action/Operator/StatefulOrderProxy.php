<?php

namespace Mmb\Action\Operator;

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
        return $this->operator->$name(...$arguments);
    }

    // todo

}