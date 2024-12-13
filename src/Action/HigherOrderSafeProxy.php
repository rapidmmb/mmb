<?php

namespace Mmb\Action;

use Illuminate\Auth\Access\AuthorizationException;
use Mmb\Auth\AreaRegister;
use Mmb\Support\Caller\AuthorizationHandleBackException;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\StatusHandleBackException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HigherOrderSafeProxy
{

    public function __construct(
        protected Action $_base,
        protected bool   $withAuthorization = true,
        protected bool   $callUsingCaller = false,
        protected ?array  $dynamicArgs = null,
    )
    {
    }

    public function __get(string $name)
    {
        if ($this->withAuthorization) {
            $this->__authorizeClass();
            $this->__authorizeProperty($name);
        }

        return $this->_base->$name;
    }

    public function __call(string $name, array $arguments)
    {
        try {
            if ($this->withAuthorization) {
                $this->__authorizeClass();
                $this->__authorizeMethod($name);
            }

            if ($this->callUsingCaller) {
                return Caller::invoke($this->_base->context, [$this->_base, $name], $arguments, $this->dynamicArgs ?? []);
            }

            return $this->_base->$name(...$arguments);
        } catch (AuthorizationException $exception) {
            if (!($exception instanceof AuthorizationHandleBackException)) {
                if ($fn = $this->_base->getDeniedHandler($exception)) {
                    throw AuthorizationHandleBackException::from($exception, [$this->_base, $fn]);
                }
            }

            throw $exception;
        } catch (HttpException $exception) {
            if (!($exception instanceof StatusHandleBackException)) {
                if ($fn = $this->_base->getDeniedHandler($exception)) {
                    throw StatusHandleBackException::from($exception, [$this->_base, $fn]);
                }
            }

            throw $exception;
        }
    }

    private function __authorizeClass()
    {
        app(AreaRegister::class)->authorize(get_class($this->_base));
    }

    private function __authorizeMethod(string $name)
    {
    }

    private function __authorizeProperty(string $name)
    {
    }

}