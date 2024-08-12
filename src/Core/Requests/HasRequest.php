<?php

namespace Mmb\Core\Requests;

use Closure;

trait HasRequest
{

    public function newRequest(string $method, array $args)
    {
        return new TelegramRequest($this, $this->info->token, $method, $args);
    }

    /**
     * Send api request
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function requestApi(string $method, array $args)
    {
        return $this->newRequest($method, $args)->request();
    }

    /**
     * Send mmb request
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function request(string $method, array $args)
    {
        $lowerMethod = strtolower($method);
        if($macro = static::$macroMethods[$lowerMethod] ?? false)
        {
            return $macro->bindTo($this, static::class)($args);
        }
        else
        {
            return $this->requestApi($method, $args);
        }
    }

    protected static $macroMethods = [];
    public static function macroMethod(string $name, Closure $callback)
    {
        static::$macroMethods[strtolower($name)] = $callback;
    }

}
