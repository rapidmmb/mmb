<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class BeMessageBuilder extends BeMessage
{

    public function __construct(
        public $mediaError = null,
        $messageError = null,
    )
    {
        parent::__construct($messageError);
    }

    public function pass(Update $update, &$value)
    {
        parent::pass($update, $value);

        $value = $update->message->build();

        if (!$value)
        {
            $this->fail(value($this->mediaError ?? __('mmb::filter.media-or-text')));
        }
    }

}
