<?php

namespace Mmb\Core\Updates\Callbacks;

use Mmb\Core\Data;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Messages\InlineMessage;
use Mmb\Core\Updates\Messages\Message;

/**
 * @property string         $id
 * @property UserInfo       $from
 * @property ?Message       $message
 * @property ?int           $inlineMessageId
 * @property string         $chatInstance
 * @property string         $data
 * @property ?string        $gameShortName
 *
 * @property ?InlineMessage $inlineMessage
 */
class Callback extends Data
{

    protected function dataCasts() : array
    {
        return [
            'id'                => 'string',
            'from'              => UserInfo::class,
            'message'           => Message::class,
            'inline_message_id' => 'string',
            'chat_instance'     => 'string',
            'callback_data'     => 'string',
            'game_short_name'   => 'string',
        ];
    }

    protected function dataShortAccess() : array
    {
        return [
            'data' => 'callback_data',
        ];
    }

    protected function getInlineMessageAttribute()
    {
        return $this->makeCache('inlineMessage', fn() => InlineMessage::make($this->inlineMessageId));
    }

    public function answer($message = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'callbackQueryId' => $this->id,
                'text'            => $message,
            ],
            $args + $namedArgs
        );

        return $this->bot()->answerCallback($args);
    }

    public function alert($message = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'callbackQueryId' => $this->id,
                'showAlert'       => true,
                'text'            => $message,
            ],
            $args + $namedArgs
        );

        return $this->bot()->answerCallback($args);
    }

}
