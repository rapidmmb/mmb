<?php

namespace Mmb\Action\Inline;

use Mmb\Action\Action;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\StepHandler;
use Mmb\Core\Updates\Update;

abstract class InlineStepHandler extends StepHandler
{

    #[Alias('C')]
    #[SafeClass]
    public $initalizeClass;

    #[Alias('M')]
    public $initalizeMethod;


    #[Alias('d')]
    #[Serialize]
    public $withinData;



    protected bool $isLoadedInlineAction = false;

    protected ?InlineAction $loadedInlineAction;

    /**
     * Load and cache the inline action
     *
     * @param Update $update
     * @return InlineAction|null
     */
    protected function getInlineAction(Update $update) : ?InlineAction
    {
        if (!$this->isLoadedInlineAction)
        {
            $this->isLoadedInlineAction = true;
            $this->loadedInlineAction = $this->makeInlineAction($update);
        }

        return $this->loadedInlineAction;
    }

    /**
     * Make new inline action instance
     *
     * @param Update $update
     * @return InlineAction|null
     */
    protected abstract function makeInlineAction(Update $update) : ?InlineAction;

    /**
     * Set the cached inline action
     *
     * @param InlineAction $inlineAction
     * @return void
     */
    public function setInlineAction(InlineAction $inlineAction) : void
    {
        $this->isLoadedInlineAction = true;
        $this->loadedInlineAction = $inlineAction;
    }


    protected array $includeEvents;

    public function getEvents() : array
    {
        if (!isset($this->includeEvents))
        {
            if (class_exists($this->initalizeClass) && is_a($this->initalizeClass, Action::class, true))
            {
                $this->includeEvents = $this->initalizeClass::getInlineUsingEvents($this->initalizeMethod);
            }
            else
            {
                $this->includeEvents = [];
            }
        }

        return $this->includeEvents;
    }


    public function handle(Update $update) : void
    {
        if ($inlineAction = $this->getInlineAction($update))
        {
            if ($inlineAction->handle($update) !== false)
            {
                return;
            }
        }

        $update->skipHandler();
    }

    public function fire(string $event, ...$args)
    {
        if (in_array(strtolower($event), $this->getEvents()))
        {
            $this->getInlineAction(app(Update::class))->fireStepEvent($event, ...$args);
        }

        return parent::fire($event, ...$args);
    }

}
