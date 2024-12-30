<?php

namespace Mmb\Action\Section\Controllers;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class CallbackControlGroupHandler implements UpdateHandling
{

    public function __construct(
        public array $classes,
    )
    {
    }

    public function handleUpdate(Context $context, Update $update)
    {
        if ($update->callbackQuery)
        {
            foreach ($this->classes as $class)
            {
                $object = $class::makeByContext($context);
                /** @var ?QueryMatchPattern $pattern */
                if($pattern = $object->getCallbackMatcher()->findPattern($update->callbackQuery->data))
                {
                    $pattern->invoke($object);
                    return;
                }
            }
        }

        $update->skipHandler();
    }

}