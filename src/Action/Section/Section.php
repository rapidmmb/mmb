<?php

namespace Mmb\Action\Section;

use Mmb\Action\Action;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Memory\Step;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\EventInstance;

class Section extends Action
{

    /**
     * Create new instance
     *
     * @param Update|null $update
     * @return static
     */
    public static function make(
        Update $update = null,
    )
    {
        return new static($update);
    }

    /**
     * Create new event instance
     *
     * @return EventInstance|static
     */
    public static function instance()
    {
        return new EventInstance(static::make());
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
        return $this->initializeInline($name, Menu::class, $args);
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
        return $this->initializeInline($name, InlineForm::class, $args);
    }

    /**
     * Call the method in the next step
     *
     * @param string $method
     * @return void
     */
    public function nextStep(string $method)
    {
        NextStepHandler::make()->for(static::class, $method)->keep();
    }

}
