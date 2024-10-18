<?php

namespace Mmb\Action\Service;

use Mmb\Core\Updates\Messages\Message;

#[\AllowDynamicProperties]
class ServiceEvent
{

    /**
     * Result message
     *
     * @var Message|null
     */
    public ?Message $message = null;

    /**
     * Result value
     *
     * @var mixed
     */
    public mixed $result = null;

    /**
     * Event failed
     *
     * @var bool
     */
    public bool $failed = false;

}