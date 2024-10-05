<?php

namespace Mmb\Action\Road\Station\Concerns;

use Closure;
use Mmb\Action\Road\Station;
use Mmb\Support\Encoding\Modes\Mode;
use Mmb\Support\Encoding\Modes\StringContent;

/**
 * @method $this message(Closure|string|StringContent|array $message)
 * @method $this messageMode(Closure|string|Mode $mode)
 * @method $this messageUsing(Closure $callback)
 * @method $this messageTextUsing(Closure $callback)
 * @method $this messagePrefix(string|StringContent|Closure $string)
 * @method $this messageSuffix(string|StringContent|Closure $string)
 */
trait SignWithMessage
{

    protected function bootSignWithMessage()
    {
        $this->defineMessage('message');
    }

    /**
     * Get the message
     *
     * @param Station $station
     * @return array
     */
    public function getMessage(Station $station)
    {
        return $this->getDefinedMessage($station, 'message');
    }

}