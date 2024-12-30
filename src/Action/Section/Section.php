<?php

namespace Mmb\Action\Section;

use Mmb\Action\Action;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\HigherOrderSafeProxy;
use Mmb\Action\Inline\DefferInlineProxy;
use Mmb\Action\Inline\InlineAction;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\EventProxy;
use function Symfony\Component\String\s;

class Section extends Action
{

    /**
     * Create new instance
     *
     * @param Context $context
     * @return static
     */
    public static function make(Context $context): static
    {
        return new static($context);
    }

    /**
     * Create new instance with safe proxy
     *
     * @param Context $context
     * @return HigherOrderSafeProxy<static>|static
     */
    public static function makeSafe(Context $context)
    {
        return static::make($context)->safe;
    }

    /**
     * Create new instance with unsafe proxy
     *
     * @param Context $context
     * @return HigherOrderSafeProxy<static>|static
     */
    public static function makeUnsafe(Context $context)
    {
        return static::make($context)->unsafe;
    }

    /**
     * Create new instance of a class with safe proxy
     *
     * @template T of Action
     * @param class-string<T> $class
     * @return HigherOrderSafeProxy<T>|T
     */
    public function newSafe(string $class)
    {
        return $class::makeByContext($this->context)->safe;
    }

    /**
     * Create new instance of a class with unsafe proxy
     *
     * @template T of Action
     * @param class-string<T> $class
     * @return HigherOrderSafeProxy<T>|T
     */
    public function newUnsafe(string $class)
    {
        return $class::makeByContext($this->context)->unsafe;
    }

    /**
     * Make menu from method
     *
     * @param string $__name
     * @param mixed ...$__args
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
     * @param string $method
     * @return void
     * @deprecated
     */
    public function nextStep(string $method, ...$args)
    {
        NextStepHandler::make()->for(static::class, $method, $args)->keep($this->context);
    }

    public function __get(string $name)
    {
        if (method_exists($this, $name) &&
            ($parameters = (new \ReflectionMethod($this, $name))->getParameters()) &&
            ($type = array_shift($parameters)->getType()) instanceof \ReflectionNamedType) {

            if (is_a($type->getName(), InlineAction::class, true)) {
                return new DefferInlineProxy($this, $type->getName(), $name);
            }

        }

        return parent::__get($name);
    }

}
