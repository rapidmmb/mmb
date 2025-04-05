<?php

namespace Mmb\Core\Client;

use Closure;

trait HasClient
{

    public function newClient(string $method, array $args, array $options = [])
    {
        return new TelegramClient($this, $this->info->token, $method, $args, $options);
    }

    /**
     * Send api request
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function requestApi(string $method, array $args, array $options = [])
    {
        return $this->newClient($method, $args, $options)->request();
    }

    /**
     * Send mmb request
     *
     * @param string $method
     * @param array $args
     * @param array $options
     * @return mixed
     */
    public function request(string $method, array $args, array $options = [])
    {
        $lowerMethod = strtolower($method);
        if($macro = static::$macroMethods[$lowerMethod] ?? false)
        {
            return $macro->bindTo($this, static::class)($args, $options);
        }
        else
        {
            return $this->requestApi($method, $args, $options);
        }
    }

    protected static $macroMethods = [];
    public static function macroMethod(string $name, Closure $callback)
    {
        static::$macroMethods[strtolower($name)] = $callback;
    }

}
