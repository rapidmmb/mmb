<?php

namespace Mmb\Action\Middle;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;

class MiddleActionHandledUpdateHandling implements UpdateHandling
{

    public array $args;

    public function __construct(
        public ?string $middleClass,
        public ?string $category,
        public string $class,
        public string $method,
        ...$args,
    )
    {
        $this->args = $args;
    }

    public function only(string $class)
    {
        $this->middleClass = $class;
        return $this;
    }

    public function for(string $category)
    {
        $this->category = $category;
        return $this;
    }

    public function handleUpdate(Update $update)
    {
        if($middle = $update->get('middle-action-handled'))
        {
            if(isset($this->middleClass) && $this->middleClass != get_class($middle))
            {
                $update->skipHandler();
                return;
            }

            if(isset($this->category) && $middle->getCategory() != $this->category)
            {
                $update->skipHandler();
                return;
            }

            $this->class::make($update)->invoke($this->method, ...$this->args);
            return;
        }

        $update->skipHandler();
    }

}
