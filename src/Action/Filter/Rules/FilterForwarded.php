<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class FilterForwarded extends FilterRule
{

    public function __construct(
        public bool $fromUser = true,
        public bool $fromChannel = true,
        public      $message = null,
        public      $messageError = null,
    )
    {
    }

    public function pass(Update $update, &$value)
    {
        if (!$update->message)
        {
            $this->fail(value($this->messageError ?? __('mmb.filter.message')));
        }

        $message = $update->message;
        if (
            !$message->isForwarded ||
            (!$this->fromUser && $message->forwardFrom) ||
            (!$this->fromChannel && $message->forwardFromChat?->type == 'channel')
        )
        {
            $this->fail(value($this->message ?? __('mmb.filter.should-forward')));
        }
    }

}
