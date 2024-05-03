<?php

namespace Mmb\Action\Section\Controllers;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;

class InlineControlHandler implements UpdateHandling
{

    public function __construct(
        public string $class,
    )
    {
    }

    public function handleUpdate(Update $update)
    {
        if($update->inlineQuery)
        {
            $object = $this->class::make();
            /** @var ?QueryMatchPattern $pattern */
            if($pattern = $object->getInlineMatcher()->findPattern($update->inlineQuery->query))
            {
                $pattern->invoke($object);
                return;
            }
        }

        $update->skipHandler();
    }

}
