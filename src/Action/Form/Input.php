<?php

namespace Mmb\Action\Form;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Mmb\Action\Filter\Filterable;
use Mmb\Action\Filter\FilterableShort;
use Mmb\Action\Filter\FilterFailException;
use Mmb\Action\Filter\HasEventFilter;
use Mmb\Action\Filter\Rules\FilterFailAnyway;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\HasEvents;
use Mmb\Support\Encoding\Text;
use Mmb\Support\KeySchema\KeyboardInterface;

/**
 * @property mixed $value
 */
class Input implements KeyboardInterface
{
    use Filterable, FilterableShort, HasEventFilter, HasEvents;
    use Conditionable;

    public bool $isCreatingMode = false;

    public Context $context;

    public function __construct(
        public Form   $form,
        public string $name,
    )
    {
        $this->context = $form->context;
    }

    public function isCreating()
    {
        return $this->isCreatingMode;
    }

    public function isLoading()
    {
        return !$this->isCreatingMode;
    }


    public $askValue;

    /**
     * Set request message
     *
     * @param $message
     * @return $this
     */
    public function ask($message)
    {
        $this->askValue = $message;
        return $this;
    }

    public array $prefixes = [];
    public array $suffixes = [];

    /**
     * Add prefix message
     *
     * @param string|Closure $message
     * @return $this
     */
    public function prefix(string|Closure $message)
    {
        $this->prefixes[] = $message;
        return $this;
    }

    /**
     * Add suffix message
     *
     * @param string|Closure $message
     * @return $this
     */
    public function suffix(string|Closure $message)
    {
        $this->suffixes[] = $message;
        return $this;
    }

    /**
     * Set request message
     *
     * @param $message
     * @return $this
     */
    public function prompt($message)
    {
        $this->askValue = $message;
        return $this;
    }

    public ?string $hintValue = null;

    /**
     * Set hint message
     *
     * @param string $message
     * @return $this
     */
    public function hint(string $message)
    {
        $this->hintValue = $message;
        return $this;
    }

    public function hintPrevious(null|string|Closure $name = null, string $mode = 'HTML')
    {
        // Skip if form is not used HasRecords
        // This is cause, maybe you want to define it, but use it in inherited classes.
        // Like EditForm extends RealForm.
        if (!method_exists($this->form, 'getRecord'))
        {
            return $this->mode($mode);
        }

        $recordValue = $this->form->getRecord()->getAttribute(is_string($name) ? $name : $this->name);

        if ($name instanceof Closure)
        {
            $value = value($name, $recordValue);
        }
        else
        {
            $value = Text::userFriendly($recordValue);
        }

        return $this->mode($mode)
            ->hint(__('mmb::form.advanced.previous-value') . "\n" . Text::mode($mode)->code($value));
    }

    protected ?string $modeValue = null;

    public function mode(string $mode)
    {
        $this->modeValue = $mode;
        return $this;
    }

    public function modeHtml()
    {
        return $this->mode('HTML');
    }

    public function modeMarkdown()
    {
        return $this->mode('MarkDown');
    }

    public function modeMarkdown2()
    {
        return $this->mode('MarkDown2');
    }

    /**
     * Add only options filter
     *
     * @return $this
     */
    public function onlyOptions($message = null)
    {
        $this->filter(new FilterFailAnyway($message ?? fn() => __('mmb::form.filter.only-options')));
        return $this;
    }

    /**
     * Run the input if condition is true, otherwise skip the input.
     * If value not passed, input will not set.
     *
     * @param      $condition
     * @param mixed $value
     * @return $this
     */
    public function if($condition, mixed $value = null)
    {
        if ($this->isCreating() && !value($condition))
        {
            if (func_num_args() > 1)
            {
                $this->value = value($value);
            }

            $this->form->next();
        }

        return $this;
    }

    /**
     * Skip the input if condition is true, otherwise run the input.
     * If value not passed, input will not set.
     *
     * @param      $condition
     * @param mixed $value
     * @return $this
     */
    public function jump($condition, mixed $value = null)
    {
        if ($this->isCreating() && value($condition))
        {
            if (func_num_args() > 1)
            {
                $this->value = value($value);
            }

            $this->form->next();
        }

        return $this;
    }

    /**
     * Skip the input if the input is filled.
     * If value not passed, input will not set.
     *
     * @param mixed $value
     * @return $this
     */
    public function jumpFilled(mixed $value = null)
    {
        if ($this->isCreating() && $this->value !== null)
        {
            if (func_num_args() > 0)
            {
                $this->value = value($value);
            }

            $this->form->next();
        }

        return $this;
    }



    /**
     * Pass update
     *
     * @param Update $update
     * @return void
     */
    public function pass(Update $update)
    {
        $this->fire('passing', $update);

        if($reaction = $this->getKeyBuilder()->getPressedAction($update))
        {
            [$type, $value] = $reaction;
            switch($type)
            {
                case FormKey::ACTION_TYPE_NORMAL:
                case FormKey::ACTION_TYPE_VALUE:
                    $this->value = $value;
                break;

                case FormKey::ACTION_TYPE_ACTION:
                    $isPassed = false;
                    $pass = function($value) use(&$isPassed)
                    {
                        $this->value = $value;
                        $isPassed = true;
                    };

                    if(is_string($value))
                    {
                        $this->fire(
                            $value,
                            update: $update,
                            pass: fn() => $pass,
                        );
                    }
                    else
                    {
                        Caller::invoke(
                            $this->form->context,
                            $value,
                            [],
                            [
                                'input'  => $this,
                                'update' => $update,
                                'form'   => $this->form,
                                'pass'   => fn() => $pass,
                            ]
                        );
                    }

                    if(!$isPassed)
                    {
                        $this->form->stop();
                    }
                break;

                default:
                    throw new \InvalidArgumentException("Unknown $type");
            }
        }
        else
        {
            $this->value = $this->passFilter($this->form->context, $update)[2];
            $this->fire('filled');
        }

        $this->fire('passed');
    }

    /**
     * Add event to listen after passed value
     *
     * @param $callback
     * @return $this
     */
    public function then($callback)
    {
        $this->listen('passed', $callback);
        return $this;
    }

    /**
     * Add event to listen after passed value
     *
     * @param $callback
     * @return $this
     */
    public function passed($callback)
    {
        $this->listen('passed', $callback);
        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function passing($callback)
    {
        $this->listen('passing', $callback);
        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function filled($callback)
    {
        $this->listen('filled', $callback);
        return $this;
    }

    /**
     * Request input
     *
     * @param mixed $message
     * @return void
     */
    public function request($message = null)
    {
        $message = value($message ?? $this->askValue);
        if(is_string($message))
        {
            $message = ['text' => $message];
        }

        // Add key
        $message['key'] = $this->getKeyBuilder()->toArray();

        $value = function ($callable) use (&$message)
        {
            return $callable instanceof Closure ?
                Caller::invoke($this->form->context, $callable, [], $this->getEventDynamicArgs('*') + ['text' => @$message['text']]) :
                $callable;
        };

        // Add prefixes & suffixes
        foreach ($this->prefixes as $prefix)
            $message['text'] = $value($prefix) . @$message['text'];
        foreach ($this->suffixes as $suffix)
            $message['text'] = @$message['text'] . $value($suffix);
        if (isset($this->hintValue))
            $message['text'] = @$message['text'] . "\n\n" . $value($this->hintValue);
        if (isset($this->modeValue))
            $message['mode'] = $this->modeValue;

        $this->fire('request', $message);
    }

    /**
     * Default fail catching
     *
     * @param FilterFailException $e
     * @param Context $context
     * @param Update $update
     * @return void
     */
    protected function defaultFailCatch(FilterFailException $e, Context $context, Update $update)
    {
        $this->form->error($e);
    }

    /**
     * Event on request
     *
     * @param $message
     * @return void
     */
    public function onRequest($message)
    {
        $this->form->fire('request', $this, $message);
    }

    /**
     * Get magic properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if($name == 'value')
        {
            return $this->form->get($this->name);
        }

        throw new \Exception(sprintf("Undefined property [%s] on [%s]", $name, static::class));
    }

    /**
     * Set magic properties
     *
     * @param string $name
     * @param        $value
     * @return void
     */
    public function __set(string $name, $value) : void
    {
        if($name == 'value')
        {
            $this->form->put($this->name, $value);
            return;
        }

        $this->$name = $value;
    }


    private bool $storeMode = false;

    /**
     * Enable storing options
     *
     * @return $this
     */
    public function store()
    {
        $this->storeMode = true;
        return $this;
    }

    /**
     * Checks store mode is enabled
     *
     * @return bool
     */
    public function isStoring()
    {
        return $this->storeMode;
    }


    /**
     * Make new form key
     *
     * @param string $text
     * @param        $value
     * @return FormKey
     */
    public function key(string $text, $value = null)
    {
        return FormKey::make(...func_get_args());
    }

    /**
     * Make new form key with action
     *
     * @param string $text
     * @param        $action
     * @return FormKey
     */
    public function keyAction(string $text, $action)
    {
        return FormKey::makeAction($text, $action);
    }


    private array $keyBuilderQueue = [];
    private FormKeyBuilder $keyBuilder;

    /**
     * Add a job to key builder queue
     *
     * @param bool   $fixed
     * @param string $method
     * @param        ...$args
     * @return void
     */
    protected function addKeyBuilderQueue(bool $fixed, string $method, ...$args)
    {
        if(isset($this->keyBuilder))
        {
            $this->keyBuilder->$method(...$args);
        }
        else
        {
            $this->keyBuilderQueue[] = [$fixed, $method, $args];
        }
    }

    /**
     * Get key builder
     *
     * @return FormKeyBuilder
     */
    public function getKeyBuilder()
    {
        if(!isset($this->keyBuilder))
        {
            $this->keyBuilder = new FormKeyBuilder($this->form, $this);

            foreach($this->keyBuilderQueue as $queue)
            {
                [$fixed, $method, $args] = $queue;
                if($fixed || !$this->isLoading() || !$this->isStoring())
                {
                    $this->keyBuilder->$method(...$args);
                }
            }

            $this->keyBuilderQueue = [];
        }

        return $this->keyBuilder;
    }

    /**
     * Load storable key map
     *
     * @param array $storableMap
     * @return void
     */
    public function mergeStorableMap(array $storableMap)
    {
        $this->getKeyBuilder()->mergeKeyMap($storableMap);
    }

    /**
     * Add key options
     *
     * @param array|Closure $options
     * @param bool          $fixed
     * @return $this
     */
    public function options(array|Closure $options, bool $fixed = false)
    {
        $this->addKeyBuilderQueue($fixed, 'schema', $options, $fixed);
        return $this;
    }

    /**
     * Add key options (fixed)
     *
     * @param array|Closure $options
     * @return $this
     */
    public function optionsFixed(array|Closure $options)
    {
        return $this->options($options, true);
    }

    /**
     * Add key header
     *
     * @param array|Closure $options
     * @param bool          $fixed
     * @return $this
     */
    public function header(array|Closure $options, bool $fixed = true)
    {
        $this->addKeyBuilderQueue($fixed, 'header', $options, $fixed);
        return $this;
    }

    /**
     * Add key footer
     *
     * @param array|Closure $options
     * @param bool          $fixed
     * @return $this
     */
    public function footer(array|Closure $options, bool $fixed = true)
    {
        $this->addKeyBuilderQueue($fixed, 'footer', $options, $fixed);
        return $this;
    }

    /**
     * Add key row
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function add(array|FormKey|string $key, $value = null)
    {
        $this->addKeyBuilderQueue(true, 'add', ...func_get_args());
        return $this;
    }

    /**
     * Add key row
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function addHeader(array|FormKey|string $key, $value = null)
    {
        $this->addKeyBuilderQueue(true, 'addHeader', ...func_get_args());
        return $this;
    }

    /**
     * Add key row
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function addFooter(array|FormKey|string $key, $value = null)
    {
        $this->addKeyBuilderQueue(true, 'addFooter', ...func_get_args());
        return $this;
    }

    /**
     * Add key to last line
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function push(array|FormKey|string $key, $value = null)
    {
        $this->addKeyBuilderQueue(true, 'push', ...func_get_args());
        return $this;
    }

    /**
     * Add key to last line
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function pushHeader(array|FormKey|string $key, $value = null)
    {
        $this->addKeyBuilderQueue(true, 'pushHeader', ...func_get_args());
        return $this;
    }

    /**
     * Add key to last line
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function pushFooter(array|FormKey|string $key, $value = null)
    {
        $this->addKeyBuilderQueue(true, 'pushFooter', ...func_get_args());
        return $this;
    }

    /**
     * Add empty key row
     *
     * @return $this
     */
    public function break()
    {
        $this->addKeyBuilderQueue(true, 'break');
        return $this;
    }

    /**
     * Add empty key row
     *
     * @return $this
     */
    public function breakHeader()
    {
        $this->addKeyBuilderQueue(true, 'breakHeader');
        return $this;
    }

    /**
     * Add empty key row
     *
     * @return $this
     */
    public function breakFooter()
    {
        $this->addKeyBuilderQueue(true, 'breakFooter');
        return $this;
    }




    /**
     * Enable default form key
     *
     * @var ?bool
     */
    public $enableDefaultFormKey = null;

    /**
     * @param bool $enable
     * @return $this
     */
    public function defaultFormKey(bool $enable = true)
    {
        $this->enableDefaultFormKey = $enable;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableDefaultFormKey()
    {
        return $this->defaultFormKey(false);
    }

    /**
     * Enable cancel key
     *
     * @var null|bool|string
     */
    public $enableCancelKey = null;

    /**
     * @param bool|string $text
     * @return $this
     */
    public function cancelKey(bool|string $text = true)
    {
        $this->enableCancelKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableCancelKey()
    {
        return $this->cancelKey(false);
    }

    /**
     * Enable skip key
     *
     * @var null|bool|string
     */
    public $enableSkipKey = null;

    /**
     * @param bool|string $text
     * @return $this
     */
    public function skipKey(bool|string $text = true)
    {
        $this->enableSkipKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableSkipKey()
    {
        return $this->skipKey(false);
    }

    /**
     * Enable without changes key
     *
     * @var null|bool|string
     */
    public $enableWithoutChangesKey = null;

    /**
     * @param bool|string $text
     * @return $this
     */
    public function withoutChangesKey(bool|string $text = true)
    {
        $this->enableWithoutChangesKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableWithoutChangesKey()
    {
        return $this->withoutChangesKey(false);
    }

    /**
     * Enable ineffective key
     *
     * @var null|bool|string
     */
    public $enableIneffectiveKey = null;

    /**
     * @param bool|string $text
     * @return $this
     */
    public function ineffectiveKey(bool|string $text = true)
    {
        $this->enableIneffectiveKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableIneffectiveKey()
    {
        return $this->ineffectiveKey(false);
    }

    /**
     * Enable previous key
     *
     * @var null|bool|string
     */
    public $enablePreviousKey = null;

    /**
     * @param bool|string $text
     * @return $this
     */
    public function previousKey(bool|string $text = true)
    {
        $this->enablePreviousKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disablePreviousKey()
    {
        return $this->previousKey(false);
    }

    /**
     * Enable to add default keys at bottom and top
     *
     * @var null|bool
     */
    public $enableMirrorKey = null;

    /**
     * @param bool $enable
     * @return $this
     */
    public function mirrorKey(bool $enable = true)
    {
        $this->enableMirrorKey = $enable;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableMirrorKey()
    {
        return $this->mirrorKey(false);
    }

    /**
     * @param string $event
     * @return array
     */
    public function getEventDynamicArgs(string $event): array
    {
        return [
            'input' => $this,
            'form' => $this->form,
            'value' => fn() => $this->value,
            ...$this->getEventDefaultDynamicArgs($event),
        ];
    }

    /**
     * Listen to entering
     *
     * @param Closure $callback
     * @return $this
     */
    public function entering(Closure $callback)
    {
        $this->listen('enter', $callback);
        return $this;
    }

    /**
     * Listen to leaving
     *
     * @param Closure $callback
     * @return $this
     */
    public function leaving(Closure $callback)
    {
        $this->listen('leave', $callback);
        return $this;
    }

    /**
     * Event before the initializer calling
     *
     * @return void
     */
    protected function onInitializing()
    {
    }

    /**
     * Event after the initializer called
     *
     * @return void
     */
    protected function onInitialized()
    {
    }

    /**
     * Event when request the input
     *
     * @return void
     */
    protected function onEnter()
    {
    }

    /**
     * Event on leaving the input
     *
     * @return void
     */
    protected function onLeave()
    {
    }

    public function addMenuSchema(array $key) : void
    {
        $this->add($key);
    }

    public function createActionKey(string $text, Closure $callback)
    {
        return $this->keyAction($text, $callback);
    }
}
