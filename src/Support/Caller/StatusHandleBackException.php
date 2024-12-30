<?php

namespace Mmb\Support\Caller;

use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Exceptions\CallableException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StatusHandleBackException extends HttpException implements CallableException
{

    public function __construct(
        public $callback,
        int $statusCode, string $message = '', \Throwable $previous = null, array $headers = [], int $code = 0
    )
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * Create an instance from HttpException instance
     *
     * @param HttpException $exception
     * @param               $callback
     * @return static
     */
    public static function from(HttpException $exception, $callback)
    {
        return new static(
            $callback,
            $exception->getStatusCode(),
            $exception->getMessage(),
            $exception,
            $exception->getHeaders(),
            $exception->getCode(),
        );
    }

    /**
     * Invoke callback
     *
     * @param Context $context
     * @return void
     */
    public function invoke(Context $context)
    {
        Caller::invoke($context, $this->callback, [], [
            'update' => $context->update,
            'context' => $context,
            'code' => $this->getStatusCode(),
            'message' => $this->getMessage(),
            'exception' => $this,
        ]);
    }

}
