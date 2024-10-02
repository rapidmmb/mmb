<?php

namespace Mmb\Core\Updates;

use Illuminate\Http\Request;
use Mmb\Action\Update\CancelHandlingException;
use Mmb\Action\Update\RepeatHandlingException;
use Mmb\Action\Update\StopHandlingException;
use Mmb\Core\Data;
use Mmb\Core\Updates\Callbacks\Callback;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Poll\Poll;
use Mmb\Core\Updates\Poll\PollAnswer;

/**
 * @property int                 $id
 * @property ?Message            $message
 * @property ?Message            $editedMessage
 * @property ?Message            $channelPost
 * @property ?Message            $editedChannelPost
 * @property ?InlineQuery        $inlineQuery
 * @property ?ChosenInlineResult $chosenInlineResult
 * @property ?Callback           $callbackQuery
 * @property ?Poll               $poll
 * @property ?PollAnswer         $pollAnswer
 * @property ?ChatMemberUpdated  $myChatMember
 * @property ?ChatMemberUpdated  $chatMember
 * @property ?ChatJoinRequest    $chatJoinRequest
 */
class Update extends Data
{

    protected function dataRules() : array
    {
        return [
            'update_id'            => 'int',
            'message'              => 'nullable|array',
            'edited_message'       => 'nullable|array',
            'channel_post'         => 'nullable|array',
            'edited_channel_post'  => 'nullable|array',
            'inline_query'         => 'nullable|array',
            'chosen_inline_result' => 'nullable|array',
            'callback_query'       => 'nullable|array',
            'poll'                 => 'nullable|array',
            'poll_answer'          => 'nullable|array',
            'my_chat_member'       => 'nullable|array',
            'chat_member'          => 'nullable|array',
            'chat_join_request'    => 'nullable|array',
        ];
    }

    protected function dataCasts() : array
    {
        return [
            'update_id'           => 'int',
            'message'             => Message::class,
            'edited_message'      => Message::class,
            'channel_post'        => Message::class,
            'edited_channel_post' => Message::class,
            // 'inline_query'         => Message::class,
            // 'chosen_inline_result' => Message::class,
            'callback_query'      => Callback::class,
            'poll'                => Poll::class,
            'poll_answer'         => PollAnswer::class,
            // 'my_chat_member'       => Message::class,
            // 'chat_member'          => Message::class,
            // 'chat_join_request'    => Message::class,
        ];
    }

    protected function dataShortAccess() : array
    {
        return [
            'id' => 'update_id',
        ];
    }

    /**
     * Find the update base message
     *
     * @return ?Message
     */
    public function getMessage()
    {
        return $this->makeCache(
            'Message',
            fn() => match (true)
            {
                null !== $this->message           => $this->message,
                null !== $this->editedMessage     => $this->editedMessage,
                null !== $this->channelPost       => $this->channelPost,
                null !== $this->editedChannelPost => $this->editedChannelPost,
                null !== $this->callbackQuery     => $this->callbackQuery->message,
                default                           => null,
            }
        );
    }

    /**
     * Find the update base chat
     *
     * @return ChatInfo|null
     */
    public function getChat()
    {
        if($message = $this->getMessage())
        {
            return $message->chat;
        }

        return null;
    }

    /**
     * Find the update base chat
     *
     * @return UserInfo|null
     */
    public function getUser()
    {
        return match (true)
        {
            null !== $this->callbackQuery             => $this->callbackQuery->from,
            // null !== $this->inlineQuery => $this->inlineQuery-> TODO
            null !== ($message = $this->getMessage()) => $message->from,
            default                                   => null,
        };
    }

    /**
     * Is update handled
     *
     * @var bool
     */
    public bool $isHandled = false;

    /**
     * Skip current handler
     *
     * @return void
     */
    public function skipHandler()
    {
        $this->isHandled = false;
    }

    /**
     * Stop handling update
     *
     * @return never
     * @throws StopHandlingException
     */
    public function stopHandling()
    {
        throw new StopHandlingException();
    }

    /**
     * Cancel handling update
     *
     * This method doesn't fire final() method.
     * For example, if in the final, user saves, it will cancel.
     *
     * @return never
     * @throws CancelHandlingException
     */
    public function cancelHandling()
    {
        throw new CancelHandlingException();
    }

    /**
     * Repeat handling update
     *
     * @return never
     * @throws RepeatHandlingException
     */
    public function repeatHandling()
    {
        throw new RepeatHandlingException();
    }

    /**
     * Handle update
     *
     * @return void
     */
    public function handle()
    {
        $this->bot()->handleUpdate($this);
    }


    protected array $localData = [];

    /**
     * Get local value
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return array_key_exists($name, $this->localData) ? $this->localData[$name] : value($default);
    }

    /**
     * Set local value
     *
     * @param string $name
     * @param        $value
     * @return void
     */
    public function put(string $name, $value)
    {
        $this->localData[$name] = $value;
    }

    /**
     * Checks has local value
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->localData);
    }

    /**
     * Forget local value
     *
     * @param string $name
     * @return void
     */
    public function forget(string $name)
    {
        unset($this->localData[$name]);
    }


    protected bool $isRespond = false;

    /**
     * Response to the update
     *
     * @param       $message
     * @param array $args
     * @param mixed ...$namedArgs
     * @return ?Message
     */
    public function response($message, array $args = [], ...$namedArgs)
    {
        if ($this->isRespond)
        {
            return $this->getMessage()->sendMessage($message, $args, ...$namedArgs);
        }
        else
        {
            $args['ignoreReply'] = true;
            $result = $this->getMessage()->replyMessage($message, $args, ...$namedArgs);
            $this->isRespond = (bool) $result;
            return $result;
        }
    }

    /**
     * Response callback query
     *
     * @param       $message
     * @param array $args
     * @param mixed ...$namedArgs
     * @return ?bool
     */
    public function tell($message = null, array $args = [], ...$namedArgs)
    {
        return $this->callbackQuery?->answer($message, $args, ...$namedArgs);
    }

}
