<?php

namespace Mmb\Action\Section\Controllers;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;

class CallbackControlHandler implements UpdateHandling
{

    public function __construct(
        public string $class,
    )
    {
    }

    public function handleUpdate(Update $update)
    {
        if($update->callbackQuery)
        {
            $object = $this->class::make($update);
            /** @var ?QueryMatchPattern $pattern */
            if($pattern = $object->getCallbackMatcher()->findPattern($update->callbackQuery->data))
            {
                $pattern->invoke($object);
                return;
            }
        }

        $update->skipHandler();
    }

}
