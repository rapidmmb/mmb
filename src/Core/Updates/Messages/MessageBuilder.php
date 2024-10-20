<?php

namespace Mmb\Core\Updates\Messages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use Mmb\Core\Data;
use Mmb\Core\Traits\HasBot;
use Mmb\Core\Updates\Files\PhotoCollection;
use Mmb\Support\Serialize\ShortableProperties;

class MessageBuilder implements ShortableProperties, Arrayable
{
    use HasBot, Macroable;

    public function __construct(
        public ?string $text = null,
        public ?Data $data = null,
    )
    {
        if ($this->data instanceof PhotoCollection)
        {
            $this->data = $this->data->getDefault();
        }
    }

    public function getShortProperties() : array
    {
        return ['text', 'data'];
    }

    public function noText()
    {
        $this->text = null;
        return $this;
    }

    public function text(string $text)
    {
        $this->text = $text;
        return $this;
    }


    public function send(array $args = [], ...$namedArgs)
    {
        if ($this->data)
        {
            return $this->data->send($args, ...$namedArgs, text: $this->text);
        }
        else
        {
            return $this->bot()->sendMessage($args, ...$namedArgs, text: $this->text);
        }
    }

    public function toArray()
    {
        return [
            'text' => $this->text, // todo...
        ];
    }

}
