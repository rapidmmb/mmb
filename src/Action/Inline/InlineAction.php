<?php

namespace Mmb\Action\Inline;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Action;
use Mmb\Action\Inline\Attributes\InlineWithPropertyAttributeContract;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Memory\ConvertableToStep;
use Mmb\Action\Memory\StepHandler;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\AttributeLoader\AttributeLoader;

abstract class InlineAction implements ConvertableToStep
{

    public function __construct(
        public Context $context,
    )
    {
    }

    protected $initializerClass = null;
    protected ?string $initializerMethod = null;

    /**
     * Set initializer method to reload with next update
     *
     * @param mixed $object
     * @param string $method
     * @return $this
     */
    public function initializer($object, string $method)
    {
        if (!is_a($object, Action::class, true)) {
            throw new \TypeError(
                sprintf(
                    "Initializer object must be instance of [%s], given [%s]",
                    Action::class,
                    is_string($object) ? $object : smartTypeOf($object),
                ),
            );
        }

        $this->initializerClass = $object;
        $this->initializerMethod = $method;

        return $this;
    }

    /**
     * Get initializer
     *
     * @return array
     */
    public function getInitializer()
    {
        return [
            is_string($this->initializerClass) ?
                $this->initializerClass :
                get_class($this->initializerClass),
            $this->initializerMethod,
        ];
    }

    /**
     * Get init name
     *
     * @return string|null
     */
    public function getInitName()
    {
        return $this->initializerMethod;
    }

    /**
     * Register the inline action
     *
     * @param InlineRegister $register
     * @return void
     */
    public function register(InlineRegister $register)
    {
    }


    /**
     * List of step events
     *
     * @var array
     */
    protected array $stepEvents = [];

    /**
     * Listen to a step event
     *
     * The event should be marked with `#[UseEvents]`
     *
     * @param string $event
     * @param Closure $callback
     * @return $this
     */
    public function onStepEvent(string $event, Closure $callback)
    {
        @$this->stepEvents[strtolower($event)][] = $callback;
        return $this;
    }

    /**
     * Fire the lost listeners
     *
     * @return void
     */
    public function fireStepEvent(string $event, ...$args)
    {
        foreach ($this->stepEvents[strtolower($event)] ?? [] as $callback) {
            $callback(...$args);
        }
    }

    /**
     * Set a callback to run when step changed
     *
     * Lost event should be marked with `#[UseEvents('lost')]`
     *
     * @param Closure $callback
     * @return $this
     */
    public function lost(Closure $callback)
    {
        return $this->onStepEvent('lost', $callback);
    }

    /**
     * Set a callback to run when update is started
     *
     * Begin event should be marked with `#[UseEvents('begin')]`
     *
     * @param Closure $callback
     * @return $this
     */
    public function beginUpdate(Closure $callback)
    {
        return $this->onStepEvent('begin', $callback);
    }

    /**
     * Set a callback to run when update is finished
     *
     * End event should be marked with `#[UseEvents('end')]`
     *
     * @param Closure $callback
     * @return $this
     */
    public function endUpdate(Closure $callback)
    {
        return $this->onStepEvent('end', $callback);
    }

    /**
     * Creating mode
     *
     * @var bool
     */
    public bool $isCreating = true;

    /**
     * Checks is creating mode
     *
     * @return bool
     */
    public function isCreating()
    {
        return $this->isCreating;
    }

    /**
     * Checks is loading mode
     *
     * @return bool
     */
    public function isLoading()
    {
        return !$this->isCreating;
    }

    /**
     * Fire callback on creating object (not invoke when user clicks)
     *
     * @param Closure(static $this): mixed $callback
     * @return $this
     */
    public function creating(Closure $callback)
    {
        if ($this->isCreating()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Fire callback on loading object (when user clicks)
     *
     * @param Closure(static $this): mixed $callback
     * @return $this
     */
    public function loading(Closure $callback)
    {
        if ($this->isLoading()) {
            $callback($this);
        }

        return $this;
    }

    protected array $storedWithData;
    protected array $withs = [];
    protected array $withsOn = [];
    public ?array $cachedWithinData = null;

    /**
     * Get within data once
     *
     * @return array|null
     */
    public function getWithinData()
    {
        if ($this->isLoading()) {
            return $this->storedWithData ?? [];
        }

        if (!isset($this->cachedWithinData)) {
            $this->makeReadyWithinData();
        }

        return $this->cachedWithinData;
    }

    /**
     * With properties
     *
     * If menu is loading, load properties from stored data
     *
     * @param string ...$names
     * @return $this
     */
    public function with(string ...$names)
    {
        array_push($this->withs, ...$names);

        if ($this->isCreating() && $this->initializerClass) {
            foreach ($names as $name) {
                if (array_key_exists($name, $this->haveData)) {
                    $value = $this->haveData[$name];

                    foreach (AttributeLoader::getPropertyAttributesOf($this->initializerClass, $name, InlineWithPropertyAttributeContract::class) as $attr) {
                        $value = $attr->getInlineWithPropertyForLoad($this->context, $this, $name, $value);
                    }

                    $this->initializerClass->$name = $value;
                    unset($this->haveData[$name]);
                }
            }
        }

        if ($this->isLoading() && isset($this->storedWithData) && $this->initializerClass) {
            foreach ($names as $name) {
                if (array_key_exists($name, $this->storedWithData)) {
                    $value = $this->storedWithData[$name];

                    foreach (AttributeLoader::getPropertyAttributesOf($this->initializerClass, $name, InlineWithPropertyAttributeContract::class) as $attr) {
                        $value = $attr->getInlineWithPropertyForLoad($this->context, $this, $name, $value);
                    }

                    $this->initializerClass->$name = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Have the properties like "with"
     *
     * If menu is loading, load properties from stored data
     *
     * @param string $namespace
     * @param object $object
     * @param string ...$names
     * @return $this
     */
    public function withOn(string $namespace, object $object, string ...$names)
    {
        $this->withsOn[] = [$object, $namespace, $names];

        if ($this->isLoading() && isset($this->storedWithData)) {
            foreach ($names as $name) {
                $storedName = $namespace . ':' . $name;
                if (array_key_exists($storedName, $this->storedWithData)) {
                    $value = $this->storedWithData[$storedName];

                    foreach (AttributeLoader::getPropertyAttributesOf($object, $name, InlineWithPropertyAttributeContract::class) as $attr) {
                        $value = $attr->getInlineWithPropertyForLoad($this->context, $this, $name, $value);
                    }

                    $object->$name = $value;
                }
            }
        }

        return $this;
    }

    protected array $haveData = [];

    /**
     * Save or reload data from menu data
     *
     * @param string $name
     * @param        $value
     * @param        $default
     * @return $this
     */
    public function have(string $name, &$value, $default = null)
    {
        if ($this->isCreating()) {
            if (func_num_args() > 2) {
                $value = value($default);
            }
        } elseif ($this->isLoading()) {
            $value = $this->storedWithData[$name];
        }

        $this->haveData[$name] = $value;
        return $this;
    }

    protected function haveAs(string $name, &$value, $saving, $loading, bool $hasDefault = false, $default = null)
    {
        if ($this->isCreating()) {
            if ($hasDefault) {
                $value = value($default);
            }

            $this->haveData[$name] = $saving($value);
        } elseif ($this->isLoading()) {
            $value = $loading(
                $this->haveData[$name] = $this->storedWithData[$name],
            );
        }

        return $this;
    }

    /**
     * Save or reload model data from menu data
     *
     * This function will save model key only
     *
     * @template T
     * @param string $name
     * @param class-string<T> $class
     * @param Model|null|T $value
     * @param null $default
     * @return $this
     */
    public function haveModel(string $name, string $class, ?Model &$value, $default = null)
    {
        return $this->haveAs(
            $name,
            $value,
            fn($model) => $this->context->finder->store($model),
            fn($key) => $this->context->finder->findOrFail($class, $key),
            func_num_args() > 3,
            $default,
        );
    }

    /**
     * Save or reload model data from menu data
     *
     * This function will save the model name and key only.
     *
     * @param string $name
     * @param array $classes
     * @param Model|null $value
     * @param null $default
     * @return $this
     */
    public function haveDynamicModel(string $name, array $classes, ?Model &$value, $default = null)
    {
        return $this->haveAs(
            $name,
            $value,
            fn($model) => $this->context->finder->storeDynamic($model),
            fn($key) => $this->context->finder->findDynamicOrFail($classes, $key),
            func_num_args() > 3,
            $default,
        );
    }

    /**
     * Save or reload enum
     *
     * This function will save the enum value only.
     *
     * @template T
     * @param string $name
     * @param class-string<T> $class
     * @param Model|null|T $value
     * @param null $default
     * @return $this
     */
    public function haveEnum(string $name, string $class, &$value, $default = null)
    {
        if (is_a($class, \UnitEnum::class)) {
            return $this->haveAs(
                $name,
                $value,
                fn($enum) => $enum->name,
                function ($key) use ($class) {
                    foreach ($class::cases() as $case) {
                        if ($case->name == $key) {
                            return $case;
                        }
                    }

                    denied(404);
                },
                func_num_args() > 3,
                $default,
            );
        } elseif (is_a($class, \BackedEnum::class)) {
            return $this->haveAs(
                $name,
                $value,
                fn($enum) => $enum->value,
                fn($key) => $class::tryFrom($key) ?? denied(404),
                func_num_args() > 3,
                $default,
            );
        } else {
            throw new \TypeError("Expected UnitEnum or BackedEnum, given {$class}");
        }
    }

    protected bool $isReady = false;

    /**
     * Load and cache keys and other data
     *
     * @return void
     */
    public function makeReady()
    {
        if ($this->isReady) {
            return;
        }

        $this->makeReadyThis();

        $this->isReady = true;
    }

    protected function makeReadyThis()
    {
        $this->makeReadyWithinData();
    }

    protected function makeReadyWithinData()
    {
        if (is_object($this->initializerClass)) {
            $this->cachedWithinData = $this->haveData;

            foreach ($this->withs as $with) {
                $value = $this->initializerClass->$with;

                foreach (AttributeLoader::getPropertyAttributesOf($this->initializerClass, $with, InlineWithPropertyAttributeContract::class) as $attr) {
                    $value = $attr->getInlineWithPropertyForStore($this->context, $this, $with, $value);
                }

                $this->cachedWithinData[$with] = $value;
            }

            foreach ($this->withsOn as [$object, $namespace, $withs]) {
                foreach ($withs as $with) {
                    $value = $object->$with;

                    foreach (AttributeLoader::getPropertyAttributesOf($object, $with, InlineWithPropertyAttributeContract::class) as $attr) {
                        $value = $attr->getInlineWithPropertyForStore($this->context, $this, $with, $value);
                    }

                    $this->cachedWithinData[$namespace . ':' . $with] = $value;
                }
            }
        }
    }

    /**
     * Get variant
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if ($this->isCreating()) {
            if (in_array($name, $this->withs) && $this->initializerClass) {
                return $this->initializerClass->$name;
            } elseif (array_key_exists($name, $this->haveData)) {
                return $this->haveData[$name];
            }
        } elseif ($this->isLoading()) {
            if (array_key_exists($name, $this->storedWithData)) {
                return $this->storedWithData[$name];
            }
        }

        return value($default);
    }

    /**
     * Get data as model
     *
     * @param string $name
     * @param string $class
     * @param        $default
     * @return Model|mixed
     *
     * @deprecated 0.0
     */
    public function getModel(string $name, string $class, $default = null)
    {
        $isDefault = false;
        $id = $this->get(
            $name,
            function () use (&$isDefault) {
                $isDefault = true;
            },
        );

        if ($isDefault) {
            return value($default);
        }

        return $this->context->finder->find($class, $id);
    }

    /**
     * Get variant
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get(
            $name,
            static function () use ($name) {
                throw new \RuntimeException("Undefined property [$name]");
            },
        );
    }

    /**
     * Save the action
     *
     * @return void
     */
    protected function saveAction()
    {
        $this->context->stepFactory->set($this);
    }


    /**
     * @var class-string
     */
    protected $stepHandlerClass;

    /**
     * Load from step
     *
     * @param InlineStepHandler $step
     * @param Update $update
     * @return void
     */
    protected function loadFromStep(InlineStepHandler $step, Update $update)
    {
        $this->storedWithData = $step->withinData ?: [];

        if (
            $step->initalizeClass &&
            is_a($step->initalizeClass, Action::class, true)
        ) {
            /** @var Action $instance */
            $instance = $step->initalizeClass::makeByContext($this->context);
            $instance->loadInlineRegister($this, $step->initalizeMethod)->register();
        }
    }

    public function loadFromData(array $data = [])
    {
        $this->isCreating = false;
        $this->storedWithData = $data + ($this->storedWithData ?? []);
        return $this;
    }

    /**
     * Save to step
     *
     * @param InlineStepHandler $step
     * @return void
     */
    protected function saveToStep(InlineStepHandler $step)
    {
        $step->setInlineAction($this);
        $this->makeReady();

        [$step->initalizeClass, $step->initalizeMethod] = $this->getInitializer();
        $step->withinData = $this->cachedWithinData ?: null;
    }

    /**
     * @return StepHandler|null
     */
    public function toStep(): ?StepHandler
    {
        $step = $this->stepHandlerClass::make();
        $this->saveToStep($step);
        return $step;
    }

    /**
     * Make new instance from step
     *
     * @param Context $context
     * @param InlineStepHandler $step
     * @param Update $update
     * @return $this
     */
    public static function makeFromStep(Context $context, InlineStepHandler $step, Update $update): static
    {
        $inline = new static($context);
        $inline->isCreating = false;
        $inline->loadFromStep($step, $update);

        return $inline;
    }

    /**
     * Handle update
     *
     * @param Update $update
     * @return mixed
     */
    public abstract function handle(Update $update);

    /**
     * Handle default invokes
     *
     * @return mixed
     */
    public abstract function invoke();

}
