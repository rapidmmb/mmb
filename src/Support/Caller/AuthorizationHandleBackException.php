<?php

namespace Mmb\Support\Caller;

use Illuminate\Auth\Access\AuthorizationException;
use Mmb\Core\Updates\Update;
use Mmb\Support\Exceptions\CallableException;
use Throwable;

class AuthorizationHandleBackException extends AuthorizationException implements CallableException
{

    public function __construct(
        public $callback,
        $message = null, $code = null, Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create an instance from AuthorizationException instance
     *
     * @param AuthorizationException $exception
     * @param                        $callback
     * @return static
     */
    public static function from(AuthorizationException $exception, $callback)
    {
        $new = new static($callback, $exception->getMessage(), $exception->getCode(), $exception);

        $new->status = $exception->status;
        $new->response = $exception->response;

        return $new;
    }

    /**
     * Invoke callback
     *
     * @param Update $update
     * @return void
     */
    public function invoke(Update $update)
    {
        Caller::invoke(
            $this->callback, [], [
                'update' => $update,
                'code' => $this->status(),
                'message' => $this->getMessage(),
                'exception' => $this,
            ],
        );
    }

}
