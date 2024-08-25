<?php

namespace Mmb\Action\Section\Controllers;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;

class InlineControlGroupHandler implements UpdateHandling
{

    public function __construct(
        public array $classes,
    )
    {
    }

    public function handleUpdate(Update $update)
    {
        if ($update->inlineQuery)
        {
            foreach ($this->classes as $class)
            {
                $object = $class::make($update);
                /** @var ?QueryMatchPattern $pattern */
                if($pattern = $object->getInlineMatcher()->findPattern($update->inlineQuery->query))
                {
                    $pattern->invoke($object);
                    return;
                }
            }
        }

        $update->skipHandler();
    }

}
