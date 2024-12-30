<?php

namespace Mmb\Action\Middle;

use Mmb\Action\Action;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Section;
use Mmb\Action\Update\StopHandlingException;
use Mmb\Action\Update\UpdateHandling;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class MiddleAction extends Section implements UpdateHandling
{

    protected function onInitializeInlineRegister(InlineRegister $register)
    {
        parent::onInitializeInlineRegister($register);

        $register->before(
            function () use ($register) {
                $register->inlineAction->with('redirectTo');

                if ($register->inlineAction->isCreating() && !$this->redirectWith) {
                    return;
                }

                $register->inlineAction->with('redirectWith');
            }
        );
    }

    /**
     * If set to true, when redirect() is calling, required() method doesn't work.
     *
     * @var bool
     */
    protected $ignoreLoop = false;

    /**
     * Middle action category
     *
     * @var string
     */
    protected $category = 'global';

    // # Abstract methods:
    // public abstract function main();
    // public abstract function isRequired();

    /**
     * Target redirect location
     *
     * @var mixed
     */
    public $redirectTo;

    /**
     * Redirect arguments
     *
     * @var array
     */
    public $redirectWith = [];

    /**
     * Set redirect path
     *
     * @param string $class
     * @param string $method
     * @param        ...$args
     * @return $this
     */
    public function at(string $class, string $method, ...$args)
    {
        $this->redirectTo = [$class, $method];
        $this->redirectWith = $args;
        return $this;
    }

    public $params;

    /**
     * Set parameters for step handler
     *
     * @param ...$args
     * @return $this
     */
    public function params(...$args)
    {
        $this->params = $args;
        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Redirect back
     *
     * @param ...$args
     * @return bool
     */
    public function redirect(...$args)
    {
        $this->context->put(static::class . '::ran', true);

        if (is_array($this->redirectTo) && count($this->redirectTo) == 2) {

            [$class, $method] = $this->redirectTo;

            if (class_exists($class) && is_a($class, Action::class, true)) {

                $class::makeByContext($this->context)->invoke($method, ...$this->redirectWith, ...$args);
                $this->context->forget(static::class . '::ran');
                return true;

            }

        } elseif ($this->redirectTo === '@step') {

            $this->context->stepFactory->set(null);
            $this->context->put('middle-action-handled', $this);
            $this->update->repeatHandling();

        }

        $this->context->forget(static::class . '::ran');

        return false;
    }

    /**
     * Get arguments and with value from an array
     *
     * @param string $method
     * @param        ...$args
     * @return array
     */
    private function getArgumentsAndWiths(string $method, ...$args)
    {
        $pass = [];
        foreach ($args as $i => $arg) {
            if (is_int($i)) {
                $pass[] = $arg;
                unset($args[$i]);
            }
        }
        foreach ((new \ReflectionMethod($this, $method))->getParameters() as $parameter) {
            if (array_key_exists($name = $parameter->getName(), $args)) {
                $pass[$name] = $args[$name];
                unset($args[$name]);
            }
        }

        return [$pass, $args];
    }

    /**
     * @param ...$args
     * @return bool
     */
    private function checksIsRequired(...$args)
    {
        if ($this->ignoreLoop && $this->context->get(static::class . '::ran')) {
            return false;
        }

        [$pass, $args] = $this->getArgumentsAndWiths('isRequired', ...$args);
        return (bool)$this->invoke('isRequired', ...$pass);
    }


    /**
     * Set pending query redirect action
     *
     * @return $this
     */
    public function redirectTo(string $class, string $method = 'main')
    {
        $this->redirectTo = [$class, $method];

        return $this;
    }

    /**
     * Detect callback point and set pending query redirect action
     *
     * @return $this
     */
    public function redirectHere()
    {
        $at = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $this->redirectTo = [$at['class'], $at['function']];

        return $this;
    }

    /**
     * Request middle action
     *
     * @param       ...$args
     * @return void
     */
    public function request(...$args)
    {
        [$pass, $args] = $this->getArgumentsAndWiths('main', ...$args);

        $this->redirectWith = $args;

        $this->invoke('main', ...$pass);
    }

    /**
     * Check requiring
     *
     * @param ...$args
     * @return bool
     */
    public function check(...$args)
    {
        return $this->checksIsRequired(...$args);
    }

    /**
     * Request the action, if is required.
     *
     * @param       ...$args
     * @return void
     * @throws StopHandlingException
     */
    public function required(...$args)
    {
        if ($this->checksIsRequired(...$args)) {
            [$pass, $args] = $this->getArgumentsAndWiths('main', ...$args);

            $this->redirectWith = $args;

            $this->invoke('main', ...$pass);
            $this->update->stopHandling();
        }
    }

    /**
     * Request the action with redirecting at called point, if is required.
     *
     * @param ...$args
     * @return void
     * @throws StopHandlingException
     */
    public function requiredHere(...$args)
    {
        $this->redirectHere()->required(...$args);
    }


    /**
     * Handle update for step handler
     *
     * @param Context $context
     * @param Update $update
     * @return void
     */
    public function handleUpdate(Context $context, Update $update)
    {
        if ($this->isMiddleActionStep()) {
            $update->skipHandler();
            return;
        }

        if ($this->checksIsRequired(...$this->params ?? [])) {
            if (is_null($this->redirectTo)) {
                $this->redirectTo = '@step';
                $this->redirectWith = [];
            }

            $this->invoke('main', ...$this->params ?? []);
            return;
        }

        $update->skipHandler();
    }

    private function isMiddleActionStep()
    {
        $step = $this->context->stepFactory->get();
        if ($step instanceof InlineStepHandler) {
            return $step->initalizeClass &&
                class_exists($step->initalizeClass) &&
                is_a($step->initalizeClass, MiddleAction::class, true);
        }

        return false;
    }

}
