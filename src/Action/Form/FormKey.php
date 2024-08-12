<?php

namespace Mmb\Action\Form;

use Mmb\Core\Updates\Update;

class FormKey
{

    public function __construct(
        public string $text,
    )
    {
        $this->text = trim($this->text);
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
    public $typeOptions;

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

    /**
     * Set type to request contact
     *
     * @return $this
     */
    public function requestContact()
    {
        $this->type = 'contact';

        return $this;
    }

    /**
     * Set type to request location
     *
     * @return $this
     */
    public function requestLocation()
    {
        $this->type = 'location';

        return $this;
    }

    /**
     * Set type to request poll
     *
     * @param ...$namedArgs
     * @return $this
     */
    public function requestPoll(...$namedArgs)
    {
        $this->type = 'poll';
        $this->typeOptions = $namedArgs;

        return $this;
    }

    /**
     * Set type to request user
     *
     * @param int $id
     * @param     ...$namedArgs
     * @return $this
     */
    public function requestUser(int $id, ...$namedArgs)
    {
        $this->type = 'user';
        $this->typeOptions = $namedArgs + ['id' => $id];

        return $this;
    }

    /**
     * Set type to request users
     *
     * @param int $id
     * @param int $max
     * @param     ...$namedArgs
     * @return $this
     */
    public function requestUsers(int $id, int $max = 10, ...$namedArgs)
    {
        $this->type = 'users';
        $this->typeOptions = $namedArgs + ['id' => $id, 'max' => $max];

        return $this;
    }

    /**
     * Set type to request chat
     *
     * @param int $id
     * @param     ...$namedArgs
     * @return $this
     */
    public function requestChat(int $id, ...$namedArgs)
    {
        $this->type = 'chat';
        $this->typeOptions = $namedArgs + ['id' => $id];

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
            'contact' => 'c',
            'location' => 'l',
            'poll' => 'p',
            'user' => 'u' . $this->typeOptions['id'],
            'users' => 'U' . $this->typeOptions['id'],
            'chat' => 'C' . $this->typeOptions['id'],
            default => '.' . $this->text,
        };
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

    public static function getActionKeyFromUpdate(Update $update)
    {
        if ($update->message)
        {
            if ($update->message->contact)
            {
                return 'c';
            }
            elseif ($update->message->location)
            {
                return 'l';
            }
            elseif ($update->message->userShared)
            {
                return 'u' . $update->message->userShared->requestId;
            }
            elseif ($update->message->usersShared)
            {
                return 'U' . $update->message->usersShared->requestId;
            }
            elseif ($update->message->chatShared)
            {
                return 'C' . $update->message->chatShared->requestId;
            }
            elseif ($update->message->poll)
            {
                return 'p';
            }
            elseif ($update->message->globalType == 'text')
            {
                return '.' . $update->message->text;
            }
        }

        return null;
    }

    public static function getReactionFrom(Update $update, $actionKey, $action)
    {
        if($action === null)
        {
            return [
                static::ACTION_TYPE_NORMAL,
                match (@$actionKey[0])
                {
                    '.' => $update->message->text,
                    'c' => $update->message->contact,
                    'l' => $update->message->location,
                    'p' => $update->message->poll,
                    'u' => $update->message->userShared,
                    'U' => $update->message->usersShared,
                    'C' => $update->message->chatShared,
                    default => $update,
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
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        switch ($this->type)
        {
            case 'contact':
                return [
                    'text' => $this->text,
                    'requestContact' => true,
                ];

            case 'location':
                return [
                    'text' => $this->text,
                    'requestLocation' => true,
                ];

            case 'user':
                return [
                    'text' => $this->text,
                    'requestUser' => $this->typeOptions,
                ];

            case 'users':
                return [
                    'text' => $this->text,
                    'requestUsers' => $this->typeOptions,
                ];

            case 'chat':
                return [
                    'text' => $this->text,
                    'requestChat' => $this->typeOptions,
                ];

            case 'poll':
                return [
                    'text' => $this->text,
                    'requestPoll' => $this->typeOptions,
                ];

            default:
                return [
                    'text' => $this->text,
                ];
        }
    }

}
