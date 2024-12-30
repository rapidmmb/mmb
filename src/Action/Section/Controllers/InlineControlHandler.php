<?php

namespace Mmb\Action\Section\Controllers;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class InlineControlHandler implements UpdateHandling
{

    public function __construct(
        public string $class,
    )
    {
    }

    public function handleUpdate(Context $context, Update $update)
    {
        if($update->inlineQuery)
        {
            $object = $this->class::makeByContext($context);
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
