<?php

namespace Mmb\Action\Event;

use Mmb\Action\Action;
use Mmb\Action\Section\Controllers\QueryMatcher;
use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;

class CommandAction extends Action implements UpdateHandling
{

    /**
     * Command
     *
     * @var string|array
     */
    protected $command;

    /**
     * Ignore case command
     *
     * @var bool
     */
    protected $ignoreCase = true;

    /**
     * Ignore spaces in command
     *
     * @var bool
     */
    protected $ignoreSpaces = false;

    /**
     * Skip spaces in command
     *
     * @var bool
     */
    protected $optionalSpaces = false;

    /**
     * Initialize matcher
     *
     * @param QueryMatcher $matcher
     * @return void
     */
    public function matcher(QueryMatcher $matcher)
    {
        foreach(is_array($this->command) ? $this->command : [$this->command] as $key => $command)
        {
            if (is_string($key))
            {
                $action = $command;
                $command = $key;
            }
            else
            {
                $action = 'handle';
            }

            $pattern = $matcher->match($command, $action);

            if($this->ignoreCase)
                $pattern->ignoreCase();

            if($this->ignoreSpaces)
                $pattern->ignoreSpaces();
            elseif($this->optionalSpaces)
                $pattern->optionalSpaces();
        }
    }

    private QueryMatcher $_matcher;

    /**
     * Get command
     *
     * @return QueryMatcher
     */
    public function getMatcher()
    {
        return $this->_matcher ??= QueryMatcher::makeFrom('command', $this, 'matcher');
    }

    public function handleUpdate(Update $update)
    {
        if($update->message?->type == 'text')
        {
            if($pattern = $this->getMatcher()->findPattern($update->message->text))
            {
                $pattern->invoke($this);
                return;
            }
        }

        $update->skipHandler();
    }


    /**
     * Make command string for arguments match
     *
     * @param ...$args
     * @return string
     */
    public static function commandFor(...$args)
    {
        return (new static)->getMatcher()->makeQuery(...$args);
    }

}
