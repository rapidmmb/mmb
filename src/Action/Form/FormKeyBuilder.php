<?php

namespace Mmb\Action\Form;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Mmb\Core\Updates\Update;
use TypeError;

class FormKeyBuilder implements Arrayable
{

    public function __construct(
        public Form  $form,
        public Input $input,
    )
    {
    }

    public array $optionsAll = [];
    public array $footersAll = [];
    public array $headersAll = [];

    private function convertOptions($options)
    {
        if($options instanceof Closure)
        {
            $options = iterator_to_array($options());
        }

        return $options;
    }

    public function schema(array|Closure $options, bool $fixed = false)
    {
        $this->optionsAll[] = [$fixed, $this->convertOptions($options)];
    }

    public function footer(array|Closure $options, bool $fixed = false)
    {
        $this->footersAll[] = [$fixed, $this->convertOptions($options)];
    }

    public function header(array|Closure $options, bool $fixed = false)
    {
        $this->headersAll[] = [$fixed, $this->convertOptions($options)];
    }


    protected bool  $isReady = false;
    protected array $keyArray;
    protected array $keyMap;
    protected array $storableKeyMap;

    /**
     * Convert key builder to array
     *
     * @return array
     */
    public function toArray()
    {
        $this->makeReady();

        return $this->keyArray;
    }

    /**
     * Get key map
     *
     * @return array
     */
    public function toMap()
    {
        $this->makeReady();

        return $this->keyMap;
    }

    /**
     * Get storable key map
     *
     * @return array
     */
    public function toStorableMap()
    {
        $this->makeReady();

        return $this->storableKeyMap;
    }

    /**
     * Make ready the key builder output/input
     *
     * @return void
     */
    public function makeReady()
    {
        if($this->isReady)
        {
            return;
        }

        $this->keyArray = [];
        $this->keyMap = [];
        $this->storableKeyMap = [];

        $this->partKeyReady($this->headersAll);
        $this->partKeyReady($this->optionsAll);
        $this->partKeyReady($this->footersAll);

        $this->isReady = true;
    }

    /**
     * Convert a part to array
     *
     * @param array $array
     * @return void
     */
    protected function partKeyReady(array $array)
    {
        foreach($array as $queue)
        {
            [$fixed, $all] = $queue;
            foreach($all as $row)
            {
                if($row === null)
                {
                    continue;
                }

                if(!is_array($row))
                {
                    throw new TypeError(
                        sprintf("Invalid type, key row should be [array], given [%s]", smartTypeOf($row))
                    );
                }

                $resultRow = [];
                foreach($row as $key)
                {
                    if($key === null)
                    {
                        continue;
                    }

                    if(!($key instanceof FormKey))
                    {
                        throw new TypeError(
                            sprintf("Invalid type, key should be [%s], given [%s]", FormKey::class, smartTypeOf($key))
                        );
                    }

                    if(!$key->enabled)
                    {
                        continue;
                    }

                    $resultRow[] = ['text' => $key->text];
                    $this->keyMap[$key->getActionKey()] = $key->getAction();

                    if(!$fixed)
                    {
                        $this->storableKeyMap[$key->getActionKey()] = $key->getAction();
                    }
                }

                if($resultRow)
                {
                    $this->keyArray[] = $resultRow;
                }
            }
        }
    }

    /**
     * Merge to key map
     *
     * @param array $map
     * @return void
     */
    public function mergeKeyMap(array $map)
    {
        $this->makeReady();

        $this->keyMap = array_replace($this->keyMap, $map);
    }

    /**
     * Get pressed key
     *
     * @param Update|null $update
     * @return array|false
     */
    public function getPressedAction(?Update $update = null)
    {
        /** @var Update $update */
        $update ??= app(Update::class);

        $this->makeReady();

        foreach($this->keyMap as $actionKey => $action)
        {
            if($result = FormKey::getReactionFrom($update, $actionKey, $action))
            {
                return $result;
            }
        }

        return false;
    }


    /**
     * Convert key value to FormKey
     *
     * @param $key
     * @return FormKey
     */
    protected function toKey($key)
    {
        if($key instanceof FormKey)
        {
            return $key;
        }

        if(is_string($key))
        {
            return FormKey::make($key);
        }

        throw new TypeError(sprintf("Expected [%s], given [%s]", FormKey::class, smartTypeOf($key)));
    }

    /**
     * Convert key list to FormKey[]
     *
     * @param $key
     * @return FormKey[]
     */
    protected function toKeyLine($key)
    {
        if(!is_array($key))
        {
            if(is_iterable($key))
            {
                $key = iterator_to_array($key);
            }
            else
            {
                $key = [$key];
            }
        }

        foreach($key as $index => $subKey)
        {
            if($subKey === null)
            {
                unset($key[$index]);
            }
            else
            {
                $key[$index] = $this->toKey($subKey);
            }
        }

        return array_values($key);
    }


    /**
     * Add new empty line
     *
     * @return $this
     */
    public function break()
    {
        $this->optionsAll[] = [true, []];

        return $this;
    }

    /**
     * Add new empty line
     *
     * @return $this
     */
    public function breakHeader()
    {
        $this->headersAll[] = [true, []];

        return $this;
    }

    /**
     * Add new empty line
     *
     * @return $this
     */
    public function breakFooter()
    {
        $this->footersAll[] = [true, []];

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
        if(is_string($key) && count(func_get_args()) > 1)
        {
            $key = FormKey::make($key, $value);
        }

        $this->addTo('options', $key);
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
        if(is_string($key) && count(func_get_args()) > 1)
        {
            $key = FormKey::make($key, $value);
        }

        $this->addTo('headers', $key);
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
        if(is_string($key) && count(func_get_args()) > 1)
        {
            $key = FormKey::make($key, $value);
        }

        $this->addTo('footers', $key);
        return $this;
    }

    /**
     * @param string               $name
     * @param array|FormKey|string $key
     * @return $this|void
     */
    protected function addTo(string $name, array|FormKey|string $key)
    {
        if(is_array($key))
        {
            $this->{$name . 'All'}[] = [true, [$this->toKeyLine($key)]];
            return $this;
        }

        $this->{$name . 'All'}[] = [true, [[$this->toKey($key)]]];
    }

    /**
     * Add key to last row
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function push(array|FormKey|string $key, $value = null)
    {
        if(is_string($key) && count(func_get_args()) > 1)
        {
            $key = [FormKey::make($key, $value)];
        }

        $this->pushTo('options', $key);
        return $this;
    }

    /**
     * Add key to last row
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function pushHeader(array|FormKey|string $key, $value = null)
    {
        if(is_string($key) && count(func_get_args()) > 1)
        {
            $key = [FormKey::make($key, $value)];
        }

        $this->pushTo('headers', $key);
        return $this;
    }

    /**
     * Add key to last row
     *
     * @param array|FormKey|string $key
     * @param                      $value
     * @return $this
     */
    public function pushFooter(array|FormKey|string $key, $value = null)
    {
        if(is_string($key) && count(func_get_args()) > 1)
        {
            $key = [FormKey::make($key, $value)];
        }

        $this->pushTo('footers', $key);
        return $this;
    }

    /**
     * @param string               $name
     * @param array|FormKey|string $key
     * @return void
     */
    private function pushTo(string $name, array|FormKey|string $key)
    {
        if(!$this->{$name . 'All'})
        {
            $this->{$name . 'All'} = [];
        }

        $lastPush = &$this->{$name . 'All'}[array_key_last($this->{$name . 'All'})][1];
        if(!$lastPush)
        {
            $lastPush[] = [];
        }
        array_push($lastPush[array_key_last($lastPush)], ...$this->toKeyLine($key));
    }

    protected bool $storeMode = false;

    /**
     * Enable store mode
     *
     * @return $this
     */
    public function storeMode()
    {
        $this->storeMode = true;
        return $this;
    }

}
