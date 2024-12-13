<?php

namespace Mmb\Action\Section;

use Mmb\Action\Action;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\InlineAction;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\EventProxy;

class Section extends Action
{

    /**
     * Create new instance
     *
     * @param Context $context
     * @return static
     */
    public static function make(
        Context $context,
    )
    {
        return new static($context);
    }

    /**
     * Create new proxy instance
     *
     * @deprecated
     * @return EventProxy|static
     */
    public static function proxy()
    {
        return EventProxy::make(static::make());
    }

    /**
     * Make menu from method
     *
     * @param string $__name
     * @param mixed  ...$__args
     * @return Menu
     */
    public function menu(string $__name, ...$__args)
    {
        return $this->createInlineRegister(Menu::class, $__name, $__args)->register();
    }

    /**
     * Make inline form from
     *
     * @param string $__name
     * @param        ...$__args
     * @return InlineForm
     */
    public function inlineForm(string $__name, ...$__args)
    {
        return $this->createInlineRegister(InlineForm::class, $__name, $__args)->register();
    }

    /**
     * Make dialog from method
     *
     * @param string $__name
     * @param        ...$__args
     * @return Dialog
     */
    public function dialog(string $__name, ...$__args)
    {
        return $this->createInlineRegister(Dialog::class, $__name, $__args)->register();
    }

    /**
     * Call the method in the next step
     *
     * todo: remove this section
     * @deprecated
     * @param string $method
     * @return void
     */
    public function nextStep(string $method)
    {
        NextStepHandler::make()->for(static::class, $method)->keep();
    }

}
