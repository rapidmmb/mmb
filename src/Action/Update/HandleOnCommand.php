<?php

namespace Mmb\Action\Update;

class HandleOnCommand extends HandleCondition
{

    public function __construct(
        public string $command,
               $action
    )
    {
        parent::__construct($action);
    }

    public function check()
    {
        return $this->update->message->isCommand($this->command); // TODO
    }

}
