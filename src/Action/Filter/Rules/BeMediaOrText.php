<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class BeMediaOrText extends BeMessage
{

    public function __construct(
        public $mediaError = null,
        $messageError = null,
    )
    {
        parent::__construct($messageError);
    }

    public function pass(Context $context, Update $update, &$value)
    {
        parent::pass($context, $update, $value);

        if ($update->message->media)
        {
            $value = $update->message->media;
            return;
        }

        if ($update->message->type == 'text')
        {
            $value = $update->message->text;
            return;
        }

        $this->fail(value($this->mediaError ?? __('mmb::filter.media-or-text')));
    }

}
