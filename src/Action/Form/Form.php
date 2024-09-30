<?php

namespace Mmb\Action\Form;

use Illuminate\Support\Collection;
use Mmb\Action\Action;
use Mmb\Action\Filter\FilterFailException;
use Mmb\Action\Form\Attributes\FormClassModifierAttributeContract;
use Mmb\Action\Form\Attributes\FormDynamicPropertyAttributeContract;
use Mmb\Action\Form\Attributes\FormMethodModifierAttributeContract;
use Mmb\Action\Form\Attributes\FormPropertyModifierAttributeContract;
use Mmb\Core\Updates\Update;
use Mmb\Support\AttributeLoader\AttributeLoader;
use Mmb\Support\Caller\HasSimpleEvents;

class Form extends Action
{
    use HasSimpleEvents;
    use Concerns\InteractWithAttributes;

    protected $inputs = null;

    protected $path = null;

    public function inputs()
    {
        return $this->inputs ?? $this->path;
    }

    private array $_cached_inputs;
    private array $_cached_path;

    /**
     * Get all inputs (value will cache)
     *
     * @return string[]
     */
    public function getInputs()
    {
        return $this->_cached_inputs ??= $this->inputs();
    }

    /**
     * Get path inputs name
     *
     * @return string[]
     */
    public function getPath()
    {
        return $this->_cached_path ??= $this->path ?? $this->getInputs();
    }

    /**
     * Make new form
     *
     * @param array       $attributes
     * @param Update|null $update
     * @return static
     */
    public static function make(array $attributes = [], Update $update = null)
    {
        $form = new static($update);
        $form->setInAttributes($attributes);

        return $form;
    }

    /**
     * Request form
     *
     * @param array       $attributes
     * @return void
     */
    public function request(array $attributes = [])
    {
        $this->with($attributes)->startForm();
    }

    private $_isBooted = false;

    /**
     * Boot form when loaded
     *
     * @return void
     */
    protected function bootForm()
    {
        if (!$this->_isBooted)
        {
            foreach (AttributeLoader::getClassAttributesOf($this, FormClassModifierAttributeContract::class) as $attr)
            {
                $attr->registerFormClassModifier($this);
            }

            foreach (get_class_methods($this) as $method)
            {
                foreach (AttributeLoader::getMethodAttributesOf(
                    $this, $method, FormMethodModifierAttributeContract::class
                ) as $attr)
                {
                    $attr->registerFormMethodModifier($this, $method);
                }
            }

            foreach ((new \ReflectionClass($this))->getProperties() as $property)
            {
                foreach (AttributeLoader::getPropertyAttributesOf(
                    $this, $property->name, FormPropertyModifierAttributeContract::class
                ) as $attr)
                {
                    $attr->registerFormPropertyModifier($this, $property->name);
                }
            }

            $this->loadDynamicAttributesFromIn();

            $this->fire('booted');

            $this->_isBooted = true;
        }
    }

    /**
     * Required attribute
     *
     * @param string $name
     * @return void
     */
    protected function attrRequired(string $name)
    {
        if (!$this->has($name))
        {
            throw new \InvalidArgumentException(
                sprintf("%s::request() required attribute [%s]", static::class, $name)
            );
        }
    }

    protected function attrModel(string $name)
    {

    }




    /**
     * Make empty input
     *
     * @param string $name
     * @return Input
     */
    public function emptyInput(string $name)
    {
        if(method_exists($this, $name))
        {
            if($parameter = @(new \ReflectionMethod($this, $name))->getParameters()[0])
            {
                $type = $parameter->getType();
                if($type instanceof \ReflectionNamedType)
                {
                    $class = $type->getName();
                    if(is_a($class, Input::class, true))
                    {
                        return new $class($this, $name);
                    }
                }
            }
        }

        return new Input($this, $name);
    }

    /**
     * Create input (requesting)
     *
     * @param string $name
     * @param bool   $isCurrent
     * @return Input
     */
    public function createInput(string $name, bool $isCurrent = false)
    {
        $input = $this->emptyInput($name);
        $input->isCreatingMode = true;
        if($isCurrent) $this->currentInput = $input;
        $this->fire('initializingInput', $input);
        if(method_exists($this, $name))
        {
            $this->invokeDynamic(
                $name, [], [
                    'input' => $input,
                    'form'  => $this,
                ]
            );
        }
        $this->fire('initializedInput', $input);

        $this->addDefaultKey($input);

        return $input;
    }

    /**
     * Loading input (filling)
     *
     * @param string $name
     * @param bool   $isCurrent
     * @return Input
     */
    public function loadInput(string $name, bool $isCurrent = true)
    {
        $input = $this->emptyInput($name);
        $input->isCreatingMode = false;
        if($isCurrent) $this->currentInput = $input;
        $this->fire('initializingInput', $input);
        if(method_exists($this, $name))
        {
            $this->invokeDynamic(
                $name, [], [
                    'input' => $input,
                    'form'  => $this,
                ]
            );
        }
        $this->fire('initializedInput', $input);

        $this->addDefaultKey($input);

        return $input;
    }


    public ?string $loadedInInput = null;
    public ?Input $currentInput = null;

    public array $loadedKeyMap;
    public array $lastKeyMap;

    public function startForm()
    {
        $this->bootForm();
        $this->handleBy(
            function()
            {
                $this->fire('start');
                $this->first();
            }
        );
    }

    public function continueForm()
    {
        $this->bootForm();
        $this->handleBy(
            function()
            {
                $this->fire('step');
                $this->pass();
                $this->next();
            }
        );
    }

    public function handleBy($callback)
    {
        try
        {
            $callback();
            $this->storeStepHandler();
        }
        catch(FilterFailException $failException)
        {
            return null;
        }
        catch(ForceActionFormException $forceAction)
        {
            if($forceAction->store)
            {
                $this->storeStepHandler();
            }

            return null;
        }
    }

    public function stop(bool $store = false)
    {
        throw new ForceActionFormException($store);
    }

    public function goto(string $name)
    {
        $input = $this->createInput($name, true);

        $this->fire('enter', $input);
        $input->request();

        $this->stop(true);
    }

    public function finish()
    {
        $this->fire('finish');
        $this->stop();
    }

    public function next()
    {
        if(!$this->currentInput)
        {
            $this->first();
        }

        if($next = $this->findNextInput($this->currentInput->name))
        {
            $this->goto($next);
        }

        $this->finish();
    }

    public function hasNext()
    {
        return $this->currentInput ? $this->findNextInput($this->currentInput->name) : $this->hasAnyInput();
    }

    public function before()
    {
        if($this->currentInput && $next = $this->findNearBeforeInput($this->currentInput->name))
        {
            $this->goto($next);
        }

        $this->first();
    }

    public function hasBefore()
    {
        return $this->currentInput && $this->findNearBeforeInput($this->currentInput->name);
    }

    public function first()
    {
        if($first = @$this->getPath()[0])
        {
            $this->goto($first);
        }

        $this->finish();
    }

    public function hasAnyInput()
    {
        return (bool) @$this->getPath();
    }

    /**
     * Reset and restart form requesting
     *
     * @return void
     */
    public function restart()
    {
        $this->reset();
        $this->startForm();
        $this->stop();
    }

    /**
     * Forget all attributes
     *
     * @return void
     */
    public function reset()
    {
        $this->attributes = [];
        // TODO: Reset dynamic attributes
    }

    public function error(string|FilterFailException $message)
    {
        if($message instanceof FilterFailException)
        {
            $message = $this->formatFilterError($message);
        }

        $this->fire('error', $message);
        $this->stop();
    }

    /**
     * Store step handler
     *
     * @return FormStepHandler
     */
    public function storeStepHandler(bool $keep = true)
    {
        $stepHandler = FormStepHandler::make();
        $stepHandler->attributes = $this->getOutAttributes() ?: null;
        $stepHandler->currentInput = $this->currentInput?->name;
        $stepHandler->keyMap =
            $this->currentInput?->isStoring() ?
                $this->currentInput?->getKeyBuilder()->toStorableMap() :
                null;
        $stepHandler->class = static::class;
        if($keep) $stepHandler->keep();

        return $stepHandler;
    }

    /**
     * Load step handler
     *
     * @param FormStepHandler $stepHandler
     * @return void
     */
    public function loadStepHandler(FormStepHandler $stepHandler)
    {
        $this->setInAttributes($stepHandler->attributes ?? []);
        $this->loadedInInput = $stepHandler->currentInput;
        $this->loadedKeyMap = $stepHandler->keyMap ?? [];
    }

    /**
     * Pass update to input
     *
     * @param string|null $name
     * @param Update|null $update
     * @return void
     */
    public function pass(?string $name = null, ?Update $update = null)
    {
        $input = $this->loadInput($name ?? $this->loadedInInput, true);

        if($input->name == $this->loadedInInput)
        {
            $input->mergeStorableMap($this->loadedKeyMap);
        }

        $input->pass($update ?? $this->update);
        $this->fire('leave', $input);
    }

    public function findInputIndex(string $name)
    {
        $index = array_search($name, $this->getPath());
        return $index === false ? -1 : $index;
    }

    public function findNextInput(string $name)
    {
        $index = $this->findInputIndex($name);

        if($index === -1)
        {
            return $this->findNearNextInput($name);
        }

        return $this->getPath()[$index + 1] ?? false;
    }

    public function findNearNextInput(string $name)
    {
        $passed = false;
        foreach($this->getInputs() as $key)
        {
            if($key == $name)
            {
                $passed = true;
            }
            elseif($passed && $this->findInputIndex($key) != -1)
            {
                return $key;
            }
        }

        return false;
    }

    public function findNearBeforeInput(string $name)
    {
        $may = false;
        foreach($this->getInputs() as $key)
        {
            if($key == $name)
            {
                break;
            }
            elseif($this->findInputIndex($key) != -1)
            {
                $may = $key;
            }
        }

        return $may;
    }

    /**
     * Cancel form
     *
     * @return void
     * @throws ForceActionFormException
     */
    public function cancel()
    {
        $this->fire('cancel');
        $this->stop();
    }


    /**
     * Enable default form key
     *
     * @var bool
     */
    protected $defaultFormKey = true;

    /**
     * Enable cancel key
     *
     * @var bool|string
     */
    protected $cancelKey = true;

    /**
     * Enable skip key
     *
     * @var bool|string
     */
    protected $skipKey = false;

    /**
     * Enable ineffective key
     *
     * @var bool
     */
    protected $ineffectiveKey = false;

    /**
     * Enable without changes key
     *
     * @var bool
     */
    protected $withoutChangesKey = false;

    /**
     * Enable previous key
     *
     * @var bool|string
     */
    protected $previousKey = false;

    /**
     * Enable to add default keys at bottom and top
     *
     * @var bool
     */
    protected $mirrorKey = false;

    /**
     * Add default keys with form and input settings
     *
     * @param Input $input
     * @return void
     */
    public function addDefaultKey(Input $input)
    {
        $defaultFormKey = $input->enableDefaultFormKey ?? $this->defaultFormKey;
        if($defaultFormKey)
        {
            $cancelKey = $input->enableCancelKey ?? $this->cancelKey;
            $skipKey = $input->enableSkipKey ?? $this->skipKey;
            $previousKey = $input->enablePreviousKey ?? $this->previousKey;
            $mirrorKey = $input->enableMirrorKey ?? $this->mirrorKey;
            $ineffectiveKey = $input->enableIneffectiveKey ?? $this->ineffectiveKey;
            $withoutChangesKey = $input->enableWithoutChangesKey ?? $this->withoutChangesKey;

            if($cancelKey !== false)
            {
                $cancel = $input->keyAction(
                    $cancelKey === true ? __('mmb::form.key.cancel') : $cancelKey,
                    fn() => $this->cancel(),
                );
            }

            if($skipKey !== false)
            {
                $skip = $input->keyAction(
                    $cancelKey === true ? __('mmb::form.key.skip') : $cancelKey,
                    fn($pass) => $pass(null),
                );
            }

            if($previousKey !== false && $this->hasBefore())
            {
                $prev = $input->keyAction(
                    $previousKey === true ? __('mmb::form.key.previous') : $previousKey,
                    fn() => $this->before(),
                );
            }

            if($ineffectiveKey !== false)
            {
                $ineffective = $input->keyAction(
                    $ineffectiveKey === true ? __('mmb::form.key.ineffective') : $ineffectiveKey,
                    function () use ($input)
                    {
                        unset($this->attributes[$input->name]);
                        $this->next();
                    },
                );
            }

            if($withoutChangesKey !== false)
            {
                $withoutChanges = $input->keyAction(
                    $withoutChangesKey === true ? __('mmb::form.key.without-changes') : $withoutChangesKey,
                    function () use ($input)
                    {
                        unset($this->attributes[$input->name]);
                        $this->next();
                    },
                );
            }

            if($mirrorKey)
                $input->addHeader([@$cancel, @$prev]);
            $input->addHeader([@$skip, @$ineffective, @$withoutChanges]);

            if($mirrorKey)
                $input->addFooter([@$skip, @$ineffective, @$withoutChanges]);

            $input->addFooter([@$cancel, @$prev]);
        }
    }


    /**
     * Format filter exception message
     *
     * @param FilterFailException $failException
     * @return string
     */
    public function formatFilterError(FilterFailException $failException)
    {
        return $failException->description;
    }

    /**
     * @return array
     */
    public function getEventDynamicArgs()
    {
        return [
            'form' => $this,
        ];
    }

    /**
     * Event on input initializing
     *
     * @param Input $input
     * @return void
     */
    public function onInitializingInput(Input $input)
    {
    }

    /**
     * Event on input initialized
     *
     * @param Input $input
     * @return void
     */
    public function onInitializedInput(Input $input)
    {
    }

    /**
     * Event on error occurred
     *
     * @param string $message
     * @return void
     */
    public function onError(string $message)
    {
        $this->update->response($message);
    }

    /**
     * Event on starting the form
     *
     * @return void
     */
    public function onStart()
    {
    }

    /**
     * Event on stepping the form
     *
     * @return void
     */
    public function onStep()
    {
    }

    /**
     * Event on entering input
     *
     * @param Input $input
     * @return void
     */
    public function onEnter(Input $input)
    {
    }

    /**
     * Event on leaving input
     *
     * @param Input $input
     * @return void
     */
    public function onLeave(Input $input)
    {
    }

    /**
     * Event on canceling form
     *
     * @return void
     */
    public function onCancel()
    {
    }

    /**
     * Event on finish
     *
     * @return void
     */
    public function onFinish()
    {
    }

    /**
     * Event on request input
     *
     * @param Input $input
     * @param       $message
     * @return void
     */
    public function onRequest(Input $input, $message)
    {
        $this->response(
            $message /* + [
                'key' => $input->getKeyBuilder()->toArray(),
            ]*/
        );
        // TODO : Remove comments
    }

}
