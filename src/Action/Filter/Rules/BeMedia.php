<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
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

    public function pass(Update $update, &$value)
    {
        parent::pass($update, $value);

        if(!$update->message->media)
        {
            $this->fail(value($this->mediaError ?? __('mmb.filter.media')));
        }

        $value = $update->message->media;
    }

}
