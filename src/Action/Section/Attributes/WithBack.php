<?php

namespace Mmb\Action\Section\Attributes;

use Attribute;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\Attributes\InlineAttributeContract;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Menu;
use Mmb\Support\Behavior\Behavior;

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
        if ($register->inlineAction instanceof Menu) {
            $register->after(function () use ($register) {
                if (
                    is_null($this->action) &&
                    !method_exists($register->target, 'back')
                ) {
                    $key = $register->inlineAction->key(__('mmb::menu.key.back'), fn() => Behavior::back($register->context, get_class($register->target)));
                } else {
                    $key = $register->inlineAction->key(__('mmb::menu.key.back'), $this->action ?? 'back');
                }

                $register->inlineAction->footer([
                    [$key],
                ]);
            });
        } elseif ($register->inlineAction instanceof InlineForm && isset($this->action)) {
            $register->inlineAction->back($this->action);
        }
    }
}
