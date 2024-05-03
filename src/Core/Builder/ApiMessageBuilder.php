<?php

namespace Mmb\Core\Builder;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Mmb\Core\Updates\Callbacks\Callback;
use Mmb\Core\Updates\Messages\Message;

class ApiMessageBuilder extends ApiBuilder
{
    use BuilderHasChat;

    protected function initialize()
    {
        $this->method('sendMessage');
    }

    public function reply($message)
    {
        if($message instanceof Message && $message->chat && $this->old('chatId') === null)
        {
            $this->to($message->chat);
        }

        $message = $this->expect(
            $message,
            [
                Message::class  => 'id',
            ],
            'message id'
        );

        return $this->put('replyToMessageId', $message);
    }

    public function text($text)
    {
        $text = $this->expect(
            $text,
            [
                Message::class     => 'text',
                \Stringable::class => fn(\Stringable $str) => "$str",
                Stringable::class  => fn(Stringable $str) => $str->toString(),
            ],
            'text'
        );

        return $this->put('text', $text);
    }

    public function caption($text)
    {
        return $this->text($text);
    }

    public function parseMode(string $mode)
    {
        return $this->put('parseMode', $mode);
    }

    public function html($text = null)
    {
        if(!is_null($text))
        {
            $this->text($text);
        }

        return $this->parseMode('html');
    }

    public function markdown($text = null)
    {
        if(!is_null($text))
        {
            $this->text($text);
        }

        return $this->parseMode('markdown');
    }

    public function markdown2($text = null)
    {
        if(!is_null($text))
        {
            $this->text($text);
        }

        return $this->parseMode('markdown2');
    }

    public function disableWebPagePreview($condition = true)
    {
        return $this->put('disableWebPagePreview', (bool) value($condition));
    }

    public function enableWebPagePreview($condition = true)
    {
        return $this->disableWebPagePreview(! value($condition));
    }

    public function disableNotification($condition = true)
    {
        return $this->put('disableNotification', (bool) value($condition));
    }

    public function enableNotification($condition = true)
    {
        return $this->disableNotification(! value($condition));
    }

    public function protectContent($condition = true)
    {
        return $this->put('protectContent', (bool) value($condition));
    }

    public function allowSendingWithoutReply($condition = true)
    {
        return $this->put('allowSendingWithoutReply', (bool) value($condition));
    }


    public function attach(string $type, $file, $caption = null)
    {
        $this->method('send' . ucfirst(Str::camel($type)));

        if($caption !== null)
        {
            $this->text($caption);
        }

        return $this->put('attach', $file);
    }

    public function document($file, $caption = null)
    {
        return $this->attach('document', $file, $caption);
    }

    public function audio($file, $caption = null)
    {
        return $this->attach('audio', $file, $caption);
    }

    public function photo($file, $caption = null)
    {
        return $this->attach('photo', $file, $caption);
    }

    public function video($file, $caption = null)
    {
        return $this->attach('video', $file, $caption);
    }

    public function voice($file, $caption = null)
    {
        return $this->attach('voice', $file, $caption);
    }



    public function send(array $args = [])
    {
        return $this->bot->makeData(
            Message::class,
            $this->request(args: $args)
        );
    }

    public function sendTo($chat, array $args = [])
    {
        return $this->to($chat)->send($args);
    }

    public function sendIgnored(array $args = [])
    {
        return $this->ignore()->send($args);
    }
    
}