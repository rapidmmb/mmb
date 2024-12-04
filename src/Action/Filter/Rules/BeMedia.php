<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class BeMedia extends BeMessage
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

        if(!$update->message->media)
        {
            $this->fail(value($this->mediaError ?? __('mmb::filter.media')));
        }

        $value = $update->message->media;
    }

}
