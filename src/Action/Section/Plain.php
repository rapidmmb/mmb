<?php

namespace Mmb\Action\Section;

use Closure;
use Mmb\Action\Inline\InlineAction;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Caller\HasEvents;

class Plain extends InlineAction
{
    use HasEvents;

    protected $stepHandlerClass = PlainStepHandler::class;

    public function invoking(Closure $callback)
    {
        $this->listen('invoking', $callback);
        return $this;
    }

    public function handling(Closure $callback)
    {
        $this->listen('handling', $callback);
        return $this;
    }

    protected function getEventOptionsOnHandling()
    {
        return [
            'call' => EventCaller::CALL_UNTIL_ACTUAL_FALSE,
            'return' => EventCaller::RETURN_ALL,
        ];
    }


    public function handle(Update $update)
    {
        $result = $this->fire('handling');

        if (count($result) == 0 || end($result) === false) {
            $update->skipHandler();
        }
    }

    public function invoke()
    {
        $this->fire('invoking');
    }

}