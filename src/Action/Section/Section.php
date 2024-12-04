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
     * @return EventProxy|static
     */
    public static function proxy()
    {
        return EventProxy::make(static::make());
    }

    /**
     * Make menu from method
     *
     * @param string $name
     * @param mixed  ...$args
     * @return Menu
     */
    public function menu(string $name, ...$args)
    {
        return $this->createInlineRegister(Menu::class, $name, $args)->register();
    }

    /**
     * Make inline form from
     *
     * @param string $name
     * @param        ...$args
     * @return InlineForm
     */
    public function inlineForm(string $name, ...$args)
    {
        return $this->createInlineRegister(InlineForm::class, $name, $args)->register();
    }

    /**
     * Make dialog from method
     *
     * @param string $name
     * @param        ...$args
     * @return Dialog
     */
    public function dialog(string $name, ...$args)
    {
        return $this->createInlineRegister(Dialog::class, $name, $args)->register();
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
