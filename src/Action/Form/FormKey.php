<?php

namespace Mmb\Action\Form;

use Mmb\Action\Section\MenuKey;
use Mmb\Core\Updates\Update;

class FormKey
{

    public function __construct(
        public string $text,
    )
    {
    }

    /**
     * Make new form key
     *
     * @param string $text
     * @param        $value
     * @return static
     */
    public static function make(string $text, $value = null)
    {
        $key = new static($text);

        if(count(func_get_args()) > 1)
        {
            $key->value($value);
        }

        return $key;
    }

    /**
     * Make new form key with custom action
     *
     * @param string $text
     * @param        $action
     * @return FormKey
     */
    public static function makeAction(string $text, $action)
    {
        return (new static($text))->action($action);
    }


    public string $type = 'text';

    public const ACTION_TYPE_NORMAL = 1;
    public const ACTION_TYPE_VALUE  = 2;
    public const ACTION_TYPE_ACTION = 3;

    public int $actionType = 1;

    public $realValue;

    public $actionValue;

    /**
     * Set real value
     *
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->actionType = static::ACTION_TYPE_VALUE;
        $this->realValue = $value;

        return $this;
    }

    /**
     * Set action mode
     *
     * @param $action
     * @return $this
     */
    public function action($action)
    {
        $this->actionType = static::ACTION_TYPE_ACTION;
        $this->actionValue = $action;

        return $this;
    }


    public bool $enabled = true;

    /**
     * Enable when condition
     *
     * @param $condition
     * @return $this
     */
    public function when($condition)
    {
        $this->enabled = (bool) value($condition);
        return $this;
    }

    /**
     * Enable unless condition
     *
     * @param $condition
     * @return $this
     */
    public function unless($condition)
    {
        $this->enabled = !value($condition);
        return $this;
    }

    /**
     * Get action key
     *
     * @return string
     */
    public function getActionKey()
    {
        return match ($this->type)
        {
            'text'     => '.' . $this->text,
            'contact'  => 'c',
            'location' => 'l',
        };
    }

    /**
     * @param Update $update
     * @param string $actionKey
     * @return bool
     */
    public static function is(Update $update, string $actionKey)
    {
        return match (@$actionKey[0])
        {
            '.'     => '.' . $update?->message?->text == $actionKey,
            'c'     => (bool) $update?->message?->contact,
            'l'     => (bool) $update?->message?->location,

            default => false,
        };
    }

    /**
     * Get reaction from update
     *
     * @param Update $update
     * @param string $actionKey
     * @param mixed $action
     * @return array|false
     */
    public static function getReactionFrom(Update $update, string $actionKey, $action)
    {
        if(!static::is($update, $actionKey))
        {
            return false;
        }

        if($action === null)
        {
            return [
                static::ACTION_TYPE_NORMAL,
                match (@$actionKey[0])
                {
                    '.' => substr($actionKey, 1),
                    'c' => $update->message->contact,
                    'l' => $update->message->location,
                }
            ];
        }

        if(is_array($action))
        {
            return [
                static::ACTION_TYPE_VALUE,
                $action[0],
            ];
        }

        return [
            static::ACTION_TYPE_ACTION,
            $action,
        ];
    }

    /**
     * Get action
     *
     * @param bool $storing
     * @return array|string|null
     */
    public function getAction(bool $storing = false) // TODO: remove storing
    {
        switch($this->actionType)
        {
            case static::ACTION_TYPE_NORMAL:
                return null;

            case static::ACTION_TYPE_VALUE:
                return [$this->realValue];

            case static::ACTION_TYPE_ACTION:
                if($storing && !is_string($this->actionValue))
                {
                    throw new \TypeError(
                        sprintf("Failed to store [%s] as key action", smartTypeOf($this->actionValue))
                    );
                }

                return $this->actionValue;
        }

        return null;
    }

}
