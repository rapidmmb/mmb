<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\Section;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

/**
 * @template T of Sign
 */
abstract class Station extends Section
{

    public function __construct(
        public readonly Road $road,

        /**
         * @var T|Sign $sign
         */
        public readonly Sign $sign,

        public string        $name,

        Update               $update = null,
    )
    {
        parent::__construct($update);
    }

    public function menu(string $name, ...$args)
    {
        $register = $this->createInlineRegister(Menu::class, $name, ...$args);
        $register->inlineAction->initializer($this->road, $this->name . '.' . $name);
        return $register->register();
    }

    public function inlineForm(string $name, ...$args)
    {
        $register = $this->createInlineRegister(InlineForm::class, $name, ...$args);
        $register->inlineAction->initializer($this->road, $this->name . '.' . $name);
        return $register->register();
    }

    public function dialog(string $name, ...$args)
    {
        $register = $this->createInlineRegister(Dialog::class, $name, ...$args);
        $register->inlineAction->initializer($this->road, $this->name . '.' . $name);
        return $register->register();
    }

    /**
     * Fire a sign event
     *
     * @param string|array|Closure $event
     * @param                      ...$args
     * @return mixed
     */
    public function fireSign(string|array|Closure $event, ...$args)
    {
        return $this->sign->fire($event, ...$args, ...$this->getDynamicArgs());
    }


    protected array $dynamicArgs = [];

    /**
     * Merge dynamic arguments
     *
     * @param array $args
     * @return $this
     */
    public function mergeDynamicArgs(array $args)
    {
        $this->dynamicArgs = array_replace($this->dynamicArgs, $args);
        return $this;
    }

    /**
     * Get list of dynamic arguments
     *
     * @return array
     */
    protected function getDynamicArgs() : array
    {
        return [
            'station' => $this,
            ...$this->dynamicArgs,
        ];
    }


    protected string $defaultAction = 'main';

    /**
     * Fire an action
     *
     * @param string $name
     * @param        ...$args
     * @return mixed
     */
    public function fireAction(string $name, ...$args)
    {
        [$normalArgs, $dynamicArgs] = Caller::splitArguments($args);

        if ($name == 'main')
        {
            $name = $this->defaultAction;
        }

        return $this->invokeDynamic($name, $normalArgs, $dynamicArgs);
    }

}