<?php

namespace Mmb\Core\Updates\Messages;

use Mmb\Core\Data;
use Mmb\Core\Updates\Data\Contact;
use Mmb\Core\Updates\Data\Dice;
use Mmb\Core\Updates\Data\Game;
use Mmb\Core\Updates\Data\Location;
use Mmb\Core\Updates\Data\Story;
use Mmb\Core\Updates\Data\Venue;
use Mmb\Core\Updates\Files\Animation;
use Mmb\Core\Updates\Files\Audio;
use Mmb\Core\Updates\Files\DataWithFile;
use Mmb\Core\Updates\Files\Document;
use Mmb\Core\Updates\Files\PhotoCollection;
use Mmb\Core\Updates\Files\Sticker;
use Mmb\Core\Updates\Files\Video;
use Mmb\Core\Updates\Files\VideoNote;
use Mmb\Core\Updates\Files\Voice;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\ChatShared;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Infos\UserShared;
use Mmb\Core\Updates\Infos\UsersShared;
use Mmb\Core\Updates\Poll\Poll;
use Ramsey\Collection\Collection;

/**
 * @property int                            $id
 * @property int                            $threadId
 * @property ?UserInfo                      $from
 * @property ?ChatInfo                      $sender
 * @property ?Date                          $date
 * @property ?ChatInfo                      $chat
 * @property ?UserInfo                      $forwardFrom
 * @property ?ChatInfo                      $forwardFromChat
 * @property ?int                           $forwardFromMessageId
 * @property ?string                        $forwardSignature
 * @property ?string                        $forwardSenderName
 * @property ?Date                          $forwardDate
 * @property ?bool                          $isTopic
 * @property ?bool                          $isAutomaticForward
 * @property ?Message                       $replyTo
 * @property ?UserInfo                      $viaBot
 * @property ?Date                          $editDate
 * @property ?bool                          $hasProtectedContent
 * @property ?string                        $mediaGroupId
 * @property ?string                        $authorSignature
 * @property ?string                        $text
 * @property ?Animation                     $animation
 * @property ?Audio                         $audio
 * @property ?Document                      $document
 * @property ?PhotoCollection               $photo
 * @property ?Sticker                       $sticker
 * @property ?Story                         $story
 * @property ?Video                         $video
 * @property ?VideoNote                     $videoNote
 * @property ?Voice                         $voice
 * @property ?string                        $caption
 * @property ?EntityCollection              $captionEntities
 * @property ?bool                          $hasMediaSpoiler
 * @property ?Contact                       $contact
 * @property ?Dice                          $dice
 * @property ?Game                          $game
 * @property ?Poll                          $poll
 * @property ?Venue                         $venue
 * @property ?Location                      $location
 * @property ?Collection<UserInfo>          $newChatMembers
 * @property ?UserInfo                      $leftChatMember
 * @property ?string                        $newChatTitle
 * @property ?PhotoCollection               $newChatPhoto
 * @property ?bool                          $deleteChatPhoto
 * @property ?bool                          $groupChatCreated
 * @property ?bool                          $supergroupChatCreated
 * @property ?bool                          $channelChatCreated
 * @property ?MessageAutoDeleteTimerChanged $messageAutoDeleteTimerChanged
 * @property ?int                           $migrateToChatId
 * @property ?int                           $migrateFromChatId
 * @property ?Message                       $pinnedMessage
 * @property ?Invoice                       $invoice
 * @property ?UserShared                    $userShared
 * @property ?UsersShared                   $usersShared
 * @property ?ChatShared                    $chatShared
 * @property ?string                        $connectedWebsite
 * @property ?WriteAccessAllowed            $writeAccessAllowed
 * @property ?InlineKeyoardMarkup           $replyMarkup
 *
 * @property string                         $type
 * @property string                         $globalType
 * @property DataWithFile                   $media
 * @property bool                           $isForwarded
 *
 * @property bool $isDeleted
 */
class Message extends Data
{

    protected function dataCasts() : array
    {
        return [
            'message_id'              => 'int',
            'message_thread_id'       => 'int',
            'from'                    => UserInfo::class,
            'sender_chat'             => ChatInfo::class,
            'date'                    => 'date',
            'chat'                    => ChatInfo::class,
            'forward_from'            => UserInfo::class,
            'forward_from_chat'       => ChatInfo::class,
            'forward_from_message_id' => 'int',
            'forward_signature'       => 'string',
            'forward_date'            => 'date',
            'is_topic_message'        => 'bool',
            'is_automatic_forward'    => 'bool',
            'reply_to_message'        => Message::class,
            'via_bot'                 => UserInfo::class,
            'edit_date'               => 'date',
            'has_protected_content'   => 'bool',
            'media_group_id'          => 'string',
            'author_signature'        => 'string',
            'text'                    => 'string',
            'animation'               => Animation::class,
            'audio'                   => Audio::class,
            'document'                => Document::class,
            'photo'                   => PhotoCollection::class,
            'sticker'                 => Sticker::class,
            'story'                   => Story::class,
            'video'                   => Video::class,
            'video_note'              => VideoNote::class,
            'voice'                   => Voice::class,
            'caption'                 => 'string',
            // 'caption_entities'        => EntityCollection::class,
            'has_media_spoiler'       => 'bool',
            'contact'                 => Contact::class,
            'dice'                    => Dice::class,
            'game'                    => Game::class,
            'poll'                    => Poll::class,
            'venue'                   => Venue::class,
            'location'                => Location::class,
            'new_chat_members'        => [UserInfo::class],
            'left_chat_member'        => UserInfo::class,
            'new_chat_title'          => 'string',
            'new_chat_photo'          => PhotoCollection::class,
            'delete_chat_photo'       => 'bool',
            'group_chat_created'      => 'bool',
            'supergroup_chat_created' => 'bool',
            'channel_chat_created'    => 'bool',
            // 'message_auto_delete_timer_changed' => MessageAutoDeleteTimerChanged,
            'migrate_to_chat_id'      => 'int',
            'migrate_from_chat_id'    => 'int',
            'pinned_message'          => Message::class,
            // 'invoice'                           => Invoice::class,
            'user_shared'             => UserShared::class,
            'users_shared'            => UsersShared::class,
            'chat_shared'             => ChatShared::class,
            'connected_website'       => 'string',
            // 'write_access_allowed'              => WriteAccessAllowed::class,
            // 'reply_markup'                      => InlineKeyboardMarkup::class,
        ];
    }

    protected function dataShortAccess() : array
    {
        return [
            'id'        => 'message_id',
            'thread_id' => 'message_thread_id',
            'reply_to'  => 'reply_to_message',
        ];
    }

    protected function getTextAttribute()
    {
        return $this->allData['caption'] ?? $this->allData['text'] ?? null;
    }

    protected function getTypeAttribute()
    {
        return $this->makeCache(
            'type', function ()
        {
            return match (true)
            {
                null !== $this->photo                 => 'photo',
                null !== $this->contact               => 'contact',
                null !== $this->location              => 'location',
                null !== $this->video                 => 'video',
                null !== $this->voice                 => 'voice',
                null !== $this->audio                 => 'audio',
                null !== $this->sticker               => 'sticker',
                null !== $this->animation             => 'animation',
                null !== $this->story                 => 'story',
                null !== $this->videoNote             => 'videoNote',
                null !== $this->dice                  => 'dice',
                null !== $this->game                  => 'game',
                null !== $this->document              => 'document',
                null !== $this->poll                  => 'poll',
                null !== $this->venue                 => 'venue',
                null !== $this->newChatMembers        => 'newChatMembers',
                null !== $this->leftChatMember        => 'leftChatMember',
                null !== $this->newChatTitle          => 'newChatTitle',
                null !== $this->deleteChatPhoto       => 'deleteChatPhoto',
                null !== $this->groupChatCreated      => 'groupChatCreated',
                null !== $this->supergroupChatCreated => 'supergroupChatCreated',
                null !== $this->channelChatCreated    => 'channelChatCreated',
                null !== $this->invoice               => 'invoice',
                null !== $this->userShared            => 'userShared',
                null !== $this->usersShared           => 'usersShared',
                null !== $this->chatShared            => 'chatShared',

                null !== $this->caption               => 'media',
                null !== $this->text                  => 'text',

                default                               => 'unknown',
            };
        }
        );
    }

    protected function getGlobalTypeAttribute()
    {
        return $this->makeCache(
            'globalType', function ()
        {
            return match ($this->type)
            {
                'text'                                                                                                                                   => 'text',
                'photo', 'video', 'voice', 'audio', 'sticker', 'animation', 'videoNote', 'document', 'media'                                             => 'media',
                'contact', 'location', 'dice', 'game', 'poll', 'invoice', 'userShared', 'usersShared', 'chatShared'                                      => 'data',
                'newChatMembers', 'leftChatMember', 'newChatTitle', 'deleteChatPhoto', 'groupChatCreated', 'supergroupChatCreated', 'channelChatCreated' => 'info',
                'story'                                                                                                                                  => '',     // TODO
                'venue'                                                                                                                                  => 'data', // TODO
                default                                                                                                                                  => 'unknown',
            };
        }
        );
    }

    protected function getMediaAttribute()
    {
        if ($this->globalType == 'media')
        {
            return $this->{$this->type};
        }

        return null;
    }

    protected function getIsForwardedAttribute() : bool
    {
        return $this->forwardFrom || $this->forwardFromChat;
    }

    protected function getIsDeletedAttribute() : bool
    {
        return $this->allData['date'] === 0;
    }


    public function build()
    {
        $media = $this->media;

        if ($this->type != 'text' && (!$media || !method_exists($media, 'send')))
        {
            return null;
        }

        return new MessageBuilder(
            $this->text,
            $media,
        );
    }


    /**
     * Is command
     *
     * @param string|array  $command
     * @param array|null   &$prompts
     * @return bool
     */
    public function isCommand(string|array $command, array &$prompts = null)
    {
        if (isset($this->allData['text']))
        {
            foreach (is_array($command) ? $command : [$command] as $command)
            {
                $pattern = '#' . $command . '#i';
                $promptNames = [];

                $pattern = preg_replace_callback(
                    '/\{([\w\d]+)(\:(.*?)|)\}/', function ($matches) use (&$promptNames)
                {
                    $pat = $matches[3] ?: '.*';
                    if ($pat == '*')
                    {
                        $pat = '[\s\S]*';
                    }

                    $promptNames[] = $matches[1];
                    return '(?<' . $matches[1] . '>' . $pat . ')';
                }, $pattern
                );

                if (preg_match($pattern, $this->text, $matches))
                {
                    $prompts = [];
                    foreach ($promptNames as $name)
                    {
                        $prompts[$name] = $matches[$name];
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Send response
     *
     * @param       $message
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     * @deprecated
     */
    public function response($message = null, array $args = [], ...$namedArgs)
    {
        return $this->replyMessage($message, $args, ...$namedArgs);
    }

    public function delete(array $args = [], ...$namedArgs)
    {
        return $this->bot()->deleteMessage(
            $args + $namedArgs + [
                'chat'      => $this->chat?->id,
                'messageId' => $this->id,
            ]
        );
    }

    public function sendMessage($message = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'chat' => $this->chat?->id,
                'text' => $message,
            ],
            $args + $namedArgs
        );

        return $this->bot()->sendMessage($args);
    }

    public function send($type = null, $message = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'chat' => $this->chat?->id,
                'type' => $type,
                'text' => $message,
            ],
            $args + $namedArgs
        );

        return $this->bot()->send($args);
    }

    public function replyMessage($message = null, array $args = [], ...$namedArgs)
    {
        return $this->sendMessage($message, $args + $namedArgs + ['reply' => $this->id]);
    }

    public function reply($type = null, $message = null, array $args = [], ...$namedArgs)
    {
        return $this->send($type, $message, $args + $namedArgs + ['reply' => $this->id]);
    }

    public function editText($message = null, array $args = [], ...$namedArgs)
    {
        if ($this->globalType == 'media')
        {
            $args = $this->mergeMultiple(
                [
                    'chat'      => $this->chat?->id,
                    'messageId' => $this->id,
                    'caption'   => $message,
                ],
                $args + $namedArgs
            );

            return $this->bot()->editMessageCaption($args);
        }
        else
        {
            $args = $this->mergeMultiple(
                [
                    'chat'      => $this->chat?->id,
                    'messageId' => $this->id,
                    'text'      => $message,
                ],
                $args + $namedArgs
            );

            return $this->bot()->editMessageText($args);
        }
    }

    public function editKey(array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'chat'      => $this->chat?->id,
                'messageId' => $this->id,
            ],
            $args + $namedArgs
        );

        return $this->bot()->editMessageReplyMarkup($args);
    }

    public function forward($chatId = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'fromChatId' => $this->chat?->id,
                'messageId'  => $this->id,
                'chat'       => $chatId,
            ],
            $args + $namedArgs
        );

        return $this->bot()->forwardMessage($args);
    }

    public function copy($chatId = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'fromChatId' => $this->chat?->id,
                'messageId'  => $this->id,
                'chat'       => $chatId,
            ],
            $args + $namedArgs
        );

        return $this->bot()->copyMessage($args);
    }

}
