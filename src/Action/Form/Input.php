<?php

namespace Mmb\Action\Form;

use Closure;
use Mmb\Action\Filter\Filterable;
use Mmb\Action\Filter\FilterableShort;
use Mmb\Action\Filter\FilterFailException;
use Mmb\Action\Filter\HasEventFilter;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\HasSimpleEvents;

/**
 * @property mixed $value
 */
class Input
{
    use Filterable, FilterableShort, HasEventFilter, HasSimpleEvents;

    public bool $isCreatingMode = false;

    public function __construct(
        public Form   $form,
        public string $name,
    )
    {
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

    public ?string $placeholderValue = null;

    /**
     * Set placeholder message
     *
     * @param string $message
     * @return $this
     */
    public function placeholder(string $message)
    {
        $this->placeholderValue = $message;
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
                        $this->fire($value);
                    }
                    else
                    {
                        Caller::invoke(
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
            $this->value = $this->passFilter($update)[2];
        }

        $this->fire('pass');
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

        $message['key'] = $this->getKeyBuilder()->toArray();

        $this->fire('request', $message);
    }

    /**
     * Default fail catching
     *
     * @param FilterFailException $e
     * @param Update              $update
     * @return void
     */
    protected function defaultFailCatch(FilterFailException $e, Update $update)
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
     * Call method if condition is true
     *
     * @param              $condition
     * @param Closure      $then
     * @param Closure|null $default
     * @return $this
     */
    public function when($condition, Closure $then, Closure $default = null)
    {
        if(value($condition))
        {
            $then($this);
        }
        elseif($default)
        {
            $default($this);
        }

        return $this;
    }

    /**
     * Call method if condition is false
     *
     * @param              $condition
     * @param Closure      $then
     * @param Closure|null $default
     * @return $this
     */
    public function until($condition, Closure $then, Closure $default = null)
    {
        if(!value($condition))
        {
            $then($this);
        }
        elseif($default)
        {
            $default($this);
        }

        return $this;
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

        error_log(sprintf("Undefined property [%s] on [%s]", $name, static::class));
        return null;
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
     * @return array
     */
    protected function getEventDynamicArgs()
    {
        return [
            'input' => $this,
            'form' => $this->form,
            'value' => fn() => $this->value,
        ];
    }

}
