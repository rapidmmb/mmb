<?php

namespace Mmb\Action\Section\Attributes;

use Attribute;
use Mmb\Action\Inline\Attributes\InlineAttribute;
use Mmb\Action\Section\Menu;

#[Attribute(Attribute::TARGET_METHOD)]
class MenuWithBack extends InlineAttribute
{

    public function __construct(
        public $action = 'back',
    )
    {
    }

    /**
     * @param Menu $inline
     */
    public function after($inline)
    {
        $inline->footer([
            [ $inline->key(__('menu.key.back'), $this->action) ],
        ]);
    }

}
