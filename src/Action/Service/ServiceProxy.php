<?php

namespace Mmb\Action\Service;

use Closure;

/**
 * @template T of Service
 */
class ServiceProxy
{

    /**
     * @param T $service
     */
    public function __construct(
        protected Service $service,
    )
    {
    }

    protected Closure $error;

    public function error(Closure $callback)
    {
        $this->error = $callback;
        return $this;
    }

    protected Closure $then;

    public function then(Closure $callback)
    {
        $this->then = $callback;
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        try
        {
            $result = $this->service->$name(...$arguments);
        }
        catch (ServiceFailed $failed)
        {
            if (isset($this->error))
            {
                return ($this->error)($failed);
            }

            throw $failed;
        }

        if (isset($this->then))
        {
            return ($this->then)($result);
        }

        return $result;
    }

}