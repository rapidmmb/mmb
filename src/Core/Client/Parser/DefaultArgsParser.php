<?php

namespace Mmb\Core\Client\Parser;

use Illuminate\Support\Str;
use Mmb\Core\Client\Parser\Keyboard\ReplyKeyboardMarkupArrayParser;
use Mmb\Core\Client\Client;

class DefaultArgsParser extends ArgsParserFactory
{

    protected function default() : array
    {
        return [
            'id'                => [
                '_'                   => 'chatId',
                'answerCallbackQuery' => 'callbackQueryId',
                'answerInlineQuery'   => 'inlineQueryId',
                'getFile'             => 'fileId',
            ],
            'chat'              => 'chatId',
            'text'              => '@parseText',
            'key'               => '@parseReplyMarkup',
            'replyMarkup'       => '@parseReplyMarkup',
            // 'menu'              => '@parseMenu',
            'msg'               => 'messageId',
            'msgId'             => 'messageId',
            'message'           => 'messageId',
            'mode'              => 'parseMode',
            'reply'             => 'replyToMessageId',
            'replyTo'           => 'replyToMessageId',
            'replyToMessage'    => 'replyToMessageId',
            'replyMessage'      => 'replyToMessageId',
            'limit'             => [
                '_'                    => 'limit',
                'createChatInviteLink' => 'memberLimit',
                'editChatInviteLink'   => 'memberLimit',
            ],
            'link'              => 'inviteLink',
            'invite'            => 'inviteLink',
            'alert'             => 'showAlert',
            'from'              => 'fromChatId',
            'fromChat'          => 'fromChatId',
            'user'              => 'userId',
            // 'results'           => '@parseResults',
            'until'             => 'untilDate',
            // 'per' TODO
            'type'              => '@parseType',
            'media'             => '@parseMedia',
            // 'medias'            => '@parseMedias',
            'anim'              => 'animation',
            'disableWebPreview' => 'disableWebPagePreview',
            'disablePreview'    => 'disableWebPagePreview',
            'disableWeb'        => 'disableWebPagePreview',
            'phone'             => 'phoneNumber',
            'name'              => [
                '_'           => 'name',
                'sendContact' => 'firstName',
            ],
            'des'               => 'description',
            'setName'           => 'stickerSetName',
            'cache'             => 'cacheTime',
            'inlineMessage'     => 'inlineMessageId',
            'inlineMsg'         => 'inlineMessageId',
            'expire'            => [
                '_'        => 'expireDate',
                'sendPoll' => 'closeDate',
            ],
            'joinRequest'       => 'createsJoinRequest',
            'anonymous'         => 'isAnonymous',
            'allowMultiple'     => 'allowMultipleAnswers',
            'period'            => 'openPeriod',
            'timer'             => 'openPeriod',
            'correct'           => 'correctOptionId',
            'correctOption'     => 'correctOptionId',
            'allowWithoutReply' => 'allowSendingWithoutReply',
            'allowFailedReply'  => 'allowSendingWithoutReply',
            'ignoreReply'       => 'allowSendingWithoutReply',
            'ignore'            => '@parseIgnore',
            'spoiler'           => 'hasSpoiler',
            'protect'           => 'protectContent',
        ];
    }

    public function parseText(Client $request, $key, $value)
    {
        if($key != 'text')
        {
            $key = Str::snake($key);
        }

        // Normal message
        if($request->isMethod('sendmessage', true))
        {
            return [$key => $value];
        }

        // Copy message
        if($request->isMethod('copymessage', true))
        {
            return ['caption' => $value];
        }

        // Send poll
        if($request->isMethod('sendpoll', true))
        {
            return ['question' => $value];
        }

        // Media message
        if($request->isSendMethod())
        {
            return ['caption' => $value];
        }

        // Edit text
        if($request->isMethod('editmessagetext', true))
        {
            return [$key => $value];
        }

        // Edit media
        if ($request->isEditMethod())
        {
            return ['caption' => $value];
        }

        // Else
        return [$key => $value];
    }

    public function parseReplyMarkup(Client $request, $key, $value)
    {
        return [
            'reply_markup' => app(ReplyKeyboardMarkupArrayParser::class)->normalize($value),
        ];
    }

    public function parseIgnore(Client $request, $key, $value)
    {
        $request->ignore = (bool) $value;
    }

    public function parseType(Client $request, $key, $value)
    {
        if ($request->isMethod('sendmessage', true))
        {
            $request->changeMethod('send' . $value);
        }
        else
        {
            throw new \InvalidArgumentException(
                sprintf("Argument [type] is using on method [%s], method [sendMessage] supported", $request->method)
            );
        }
    }

    public function parseMedia(Client $request, $key, $value)
    {
        if ($request->isSendMethod() && $type = substr($request->lowerMethod(), 4))
        { }
        elseif (isset($request->args['type']))
        {
            $type = $request->args['type'];
        }
        else
        {
            throw new \InvalidArgumentException("Argument [media] is using without any type");
        }

        if ($value && $type != 'text')
        {
            return [
                $type => $value,
            ];
        }

        return [];
    }

}
