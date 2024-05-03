<?php

namespace Mmb\Core\Requests\Parser;

use Illuminate\Support\Str;
use Mmb\Core\Requests\Parser\Keyboard\ReplyKeyboardMarkupArrayParser;
use Mmb\Core\Requests\RequestApi;

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
            'message'           => 'messageId',
            'mode'              => 'parseMode',
            'reply'             => 'replyToMessageId',
            'replyTo'           => 'replyToMessageId',
            'replyToMessage'    => 'replyToMessageId',
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
            // 'media'             => '@parseMedia',
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

    public function parseText(RequestApi $request, $key, $value)
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

        // Else
        return [$key => $value];
    }

    public function parseReplyMarkup(RequestApi $request, $key, $value)
    {
        return [
            'reply_markup' => app(ReplyKeyboardMarkupArrayParser::class)->normalize($value),
        ];
    }

    public function parseIgnore(RequestApi $request, $key, $value)
    {
        $request->ignore = (bool) $value;
    }

}
