<?php

namespace Mmb\Action\Section\Attributes;

use Attribute;
use Mmb\Action\Inline\Attributes\InlineAttribute;
use Mmb\Action\Inline\Attributes\InlineAttributeContract;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Menu;

#[Attribute(Attribute::TARGET_METHOD)]
class MenuWithBack implements InlineAttributeContract
{

    public function __construct(
        public $action = 'back',
    )
    {
    }

    public function registerInline(InlineRegister $register)
    {
        if ($register->inlineAction instanceof Menu)
        {
            $register->after(function () use ($register)
            {
                $register->inlineAction->footer([
                    [ $register->inlineAction->key(__('menu.key.back'), $this->action) ],
                ]);
            });
        }
    }
}
