<?php

namespace Mmb\Core\Traits;

use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\ChatMember;

trait ApiBotChats
{

    // public const ACTION_TYPING = 'typing';
    // public const ACTION_UPLOAD_PHOTO = 'upload_photo';
    // public const ACTION_UPLOAD_VIDEO = 'upload_video';
    // public const ACTION_UPLOAD_VIDEO_NOTE = 'upload_video_note';
    // public const ACTION_UPLOAD_VIOCE = 'upload_voice';
    // public const ACTION_UPLOAD_DOC = 'upload_document';
    // public const ACTION_RECORD_VIDEO = 'record_video';
    // public const ACTION_RECORD_VIDEO_NOTE = 'record_video_note';
    // public const ACTION_RECORD_VIOCE = 'record_voice';
    // public const ACTION_CHOOSE_STICKER = 'choose_sticker';
    // public const ACTION_FIND_LOCATION = 'find_location';


    /**
     * Send chat action
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return false
     */
    public function sendChatAction(array $args = [], ...$namedArgs)
    {
        return $this->request('sendChatAction', $args + $namedArgs);
    }

    public function banChatMember(array $args = [], ...$namedArgs)
    {
        return $this->request('banChatMember', $args + $namedArgs);
    }

    public function restrictChatMember(array $args = [], ...$namedArgs)
    {
        return $this->request('restrictChatMember', $args + $namedArgs);
    }

    public function promoteChatMember(array $args = [], ...$namedArgs)
    {
        return $this->request('promoteChatMember', $args + $namedArgs);
    }

    public function setChatPermissions(array $args = [], ...$namedArgs)
    {
        return $this->request('setChatPermissions', $args + $namedArgs);
    }

    public function setChatPhoto(array $args = [], ...$namedArgs)
    {
        return $this->request('setChatPhoto', $args + $namedArgs);
    }

    public function deleteChatPhoto(array $args = [], ...$namedArgs)
    {
        return $this->request('deleteChatPhoto', $args + $namedArgs);
    }

    public function setChatTitle(array $args = [], ...$namedArgs)
    {
        return $this->request('setChatTitle', $args + $namedArgs);
    }

    public function setChatDescription(array $args = [], ...$namedArgs)
    {
        return $this->request('setChatDescription', $args + $namedArgs);
    }

    public function pinChatMessage(array $args = [], ...$namedArgs)
    {
        return $this->request('pinChatMessage', $args + $namedArgs);
    }

    public function unpinChatMessage(array $args = [], ...$namedArgs)
    {
        return $this->request('unpinChatMessage', $args + $namedArgs);
    }

    public function unpinAllChatMessages(array $args = [], ...$namedArgs)
    {
        return $this->request('unpinAllChatMessages', $args + $namedArgs);
    }

    public function leaveChat(array $args = [], ...$namedArgs)
    {
        return $this->request('leaveChat', $args + $namedArgs);
    }

    public function getChat(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            ChatInfo::class,
            $this->request('getChat', $args + $namedArgs)
        );
    }

    public function getChatAdministrators(array $args = [], ...$namedArgs)
    {
        return $this->makeDataCollection(
            ChatMember::class,
            $this->request('getChatAdministrators', $args + $namedArgs)
        );
    }

    public function getChatMemberCount(array $args = [], ...$namedArgs)
    {
        return $this->request('getChatMemberCount', $args + $namedArgs);
    }

    public function getChatMember(array $args = [], ...$namedArgs)
    {
        return $this->makeDataCollection(
            ChatMember::class,
            $this->request('getChatMember', $args + $namedArgs)
        );
    }

    /**
     * @param array $args
     * @param       ...$namedArgs
     * @return string|false|null
     */
    public function exportChatInviteLink(array $args = [], ...$namedArgs)
    {
        return $this->request('exportChatInviteLink', $args + $namedArgs);
    }

    public function createChatInviteLink(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            Invite::class, // TODO
            $this->request('createChatInviteLink', $args + $namedArgs)
        );
    }

    public function editChatInviteLink(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            Invite::class, // TODO
            $this->request('editChatInviteLink', $args + $namedArgs)
        );
    }

    public function revokeChatInviteLink(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            Invite::class, // TODO
            $this->request('revokeChatInviteLink', $args + $namedArgs)
        );
    }

    public function approveChatJoinRequest(array $args = [], ...$namedArgs)
    {
        return $this->request('approveChatJoinRequest', $args + $namedArgs);
    }

    public function declineChatJoinRequest(array $args = [], ...$namedArgs)
    {
        return $this->request('declineChatJoinRequest', $args + $namedArgs);
    }

}
