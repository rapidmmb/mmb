<?php

namespace Mmb\Action\Section\Attributes;

use Attribute;
use Mmb\Action\Inline\Attributes\InlineAttribute;
use Mmb\Action\Inline\Attributes\InlineAttributeContract;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Menu;
use Mmb\Auth\AreaRegister;

#[Attribute(Attribute::TARGET_METHOD)]
class WithBack implements InlineAttributeContract
{

    public function __construct(
        public ?string $action = null,
    )
    {
    }

    public function registerInline(InlineRegister $register)
    {
        if ($register->inlineAction instanceof Menu)
        {
            $register->after(function () use ($register)
            {
                if (
                    is_null($this->action) &&
                    !method_exists($register->target, 'back') &&
                    is_array($back = app(AreaRegister::class)->getAttribute(get_class($register->target), 'back'))
                )
                {
                    $key = $register->inlineAction->keyFor(__('mmb.menu.key.back'), ...$back);
                }
                else
                {
                    $key = $register->inlineAction->key(__('mmb.menu.key.back'), $this->action ?? 'back');
                }

                $register->inlineAction->footer([
                    [ $key ],
                ]);
            });
        }
    }
}
