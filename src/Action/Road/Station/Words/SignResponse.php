<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Core\Updates\Messages\Message;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignResponse extends SignWord
{

    protected Closure $callback;

    /**
     * @param Closure $callback
     * @return T
     */
    public function by(Closure $callback)
    {
        $this->callback = $callback;
        return $this->sign;
    }

    /**
     * @return T
     */
    public function bySend()
    {
        return $this->by(function (array $args) {
            return $this->context->update->getChat()->send($args);
        });
    }

    /**
     * @return T
     */
    public function byReply()
    {
        return $this->by(function (array $args) {
            return $this->context->update->getMessage()?->reply($args) ??
                $this->context->update->getChat()->send($args);
        });
    }

    /**
     * @return T
     */
    public function byResponse()
    {
        return $this->by(function (array $args) {
            return $this->context->update->response($args);
        });
    }


    public function response(array $args): ?Message
    {
        return isset($this->callback) ?
            $this->call($this->callback, $args) :
            $this->context->update->response($args);
    }

}