<?php

namespace Mmb\Core\Traits;

use Mmb\Core\Builder\ApiMessageBuilder;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Messages\Message;

trait ApiBotMessages
{

    public function newMessage()
    {
        return ApiMessageBuilder::make($this);
    }

    public function sendMessage(array $args = [], ...$namedArgs)
    {
        $args = $args + $namedArgs;

        if(isset($args['type']))
        {
            return $this->send($args);
        }

        return $this->makeData(
            Message::class,
            $this->request('sendMessage', $args)
        );
    }

    public function send($type = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'type' => $type,
            ],
            $args + $namedArgs
        );

        // Message type
        if(isset($args['type']))
        {
            $type = $args['type'];
            unset($args['type']);
        }
        else
        {
            $type = 'text';
        }

        // Normal text message
        if($type == 'text' || $type == 'message')
        {
            unset($args['value']);
            return $this->sendMessage($args);
        }

        // Copy message
        elseif($type == 'copy')
        {
            return $this->copyMessage($args);
        }

        // Forward message
        elseif($type == 'for' || $type == 'forward')
        {
            return $this->forwardMessage($args);
        }

        // Other message
        else
        {
            if($type == "doc")
                $type = "document";
            elseif($type == "anim")
                $type = "animation";

            if(isset($args['val']))
            {
                $args[strtolower($type)] = $args['val'];
                unset($args['val']);
            }
            elseif(isset($args['value']))
            {
                $args[strtolower($type)] = $args['value'];
                unset($args['value']);
            }

            return $this->makeData(
                Message::class,
                $this->request('send' . $type, $args),
            );
        }
    }

    public function sendDocument(array $args = [], ...$namedArgs)
    {
        return $this->send('document', $args, ...$namedArgs);
    }

    public function sendPhoto(array $args = [], ...$namedArgs)
    {
        return $this->send('photo', $args, ...$namedArgs);
    }

    public function sendVoice(array $args = [], ...$namedArgs)
    {
        return $this->send('voice', $args, ...$namedArgs);
    }

    public function sendVideo(array $args = [], ...$namedArgs)
    {
        return $this->send('video', $args, ...$namedArgs);
    }

    public function sendSticker(array $args = [], ...$namedArgs)
    {
        return $this->send('sticker', $args, ...$namedArgs);
    }

    public function sendAnimation(array $args = [], ...$namedArgs)
    {
        return $this->send('animation', $args, ...$namedArgs);
    }

    public function sendAudio(array $args = [], ...$namedArgs)
    {
        return $this->send('audio', $args, ...$namedArgs);
    }

    public function sendContact(array $args = [], ...$namedArgs)
    {
        return $this->send('contact', $args, ...$namedArgs);
    }

    public function sendLocation(array $args = [], ...$namedArgs)
    {
        return $this->send('location', $args, ...$namedArgs);
    }

    public function sendVideoNode(array $args = [], ...$namedArgs)
    {
        return $this->send('videoNode', $args, ...$namedArgs);
    }

    public function sendPoll(array $args = [], ...$namedArgs)
    {
        return $this->send('poll', $args, ...$namedArgs);
    }

    public function sendDice(array $args = [], ...$namedArgs)
    {
        return $this->send('dice', $args, ...$namedArgs);
    }

    /**
     * Delete message
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return bool
     */
    public function deleteMessage(array $args = [], ...$namedArgs)
    {
        return $this->request('deleteMessage', $args + $namedArgs);
    }

    public function editMessageText(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            Message::class,
            $this->request('editMessageText', $args + $namedArgs)
        );
    }

    public function editMessageReplyMarkup(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            Message::class,
            $this->request('editMessageReplyMarkup', $args + $namedArgs)
        );
    }

    /**
     * Copy message
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return ?Message
     */
    public function copyMessage(array $args = [], ...$namedArgs)
    {
        $args = $args + $namedArgs;

        return tap(
            $this->makeData(
                Message::class,
                $this->request('copyMessage', $args)
            ),
            function(?Message $message) use($args)
            {
                if($message && !$message->chat)
                {
                    $message->chat = ChatInfo::make([
                        'id' => $args['chatId'] ?? $args['chat_id'] ?? $args['chat'] ?? $args['id'] ?? $args['to'],
                    ]);
                    // TODO: simpler this
                }
            }
        );
    }

    /**
     * Forward message
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     */
    public function forwardMessage(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            Message::class,
            $this->request('forwardMessage', $args + $namedArgs)
        );
    }

}
