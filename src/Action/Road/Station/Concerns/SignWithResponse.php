<?php

namespace Mmb\Action\Road\Station\Concerns;

use Closure;
use Mmb\Action\Road\Station;
use Mmb\Support\Caller\EventCaller;

trait SignWithResponse
{

    /**
     * Set response method
     *
     * @param Closure(array $args): mixed $callback
     * @return $this
     */
    public function responseUsing(Closure $callback)
    {
        return $this->listen('responseUsing', $callback);
    }

    /**
     * Set response method to sending
     *
     * @return $this
     */
    public function responseBySend()
    {
        return $this->responseUsing(
            fn ($args, Station $station) => $station->update()->getChat()->send($args)
        );
    }

    /**
     * Set response method to replying
     *
     * @return $this
     */
    public function responseByReply()
    {
        return $this->responseUsing(
            fn ($args, Station $station) => $station->update()->getMessage() ?
                $station->update()->getMessage()->reply($args) :
                $station->update()->getChat()->send($args)
        );
    }

    protected function getEventOptionsOnResponse()
    {
        return [
            'call' => EventCaller::CALL_ONLY_LAST,
        ];
    }

    protected function onResponse(array $args, Station $station)
    {
        return $station->response($args);
    }

}