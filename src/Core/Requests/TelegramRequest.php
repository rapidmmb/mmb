<?php

namespace Mmb\Core\Requests;

use Closure;
use GuzzleHttp\Client;
use Mmb\Core\Requests\Exceptions\TelegramResponseException;
use Mmb\Core\Requests\Exceptions\TelegramException;

class TelegramRequest extends RequestApi
{

    protected function execute()
    {
        $client = $this->getClient();
        $response = $client->post(
            "https://api.telegram.org/bot{$this->token}/{$this->method}",
            $this->getOptions(
                [
                    'query'       => $this->getFinalArgs(),
                    'http_errors' => false,
                    'proxy' => '192.168.96.216:10809',
                ]
            )
        );

        $contents = $response->getBody()->getContents();
        $json = @json_decode($contents, true);
        if(!$json)
        {
            throw new TelegramResponseException("Invalid telegram response");
        }

        if(!@$json['ok'])
        {
            throw match ($json['error_code'])
            {
                default => new TelegramException("Telegram error: " . $json['description'] . " ($json[error_code])"),
            };
        }

        return $json['result'];
    }

    public function getFinalArgs()
    {
        $args = $this->parsedArgs();
        foreach($args as $name => $value)
        {
            if(is_array($value))
            {
                $args[$name] = json_encode($value);
            }
        }

        return $args;
    }

    protected static $creatingClient = [];

    public static function creatingClient(Closure $callback)
    {
        static::$creatingClient[] = $callback;
    }

    protected static $createdClient = [];

    public static function createdClient(Closure $callback)
    {
        static::$createdClient[] = $callback;
    }

    public function getClient()
    {
        // Fire creatingClient event
        // If callbacks not returns any Client, create new one
        $client = null;
        foreach(static::$creatingClient as $callback)
        {
            $client0 = $callback->bindTo($this)($client);
            if($client0 !== null)
            {
                $client = $client0;
            }
        }

        $client ??= new Client();

        // Fire createdClient events
        // This callbacks will modify client
        foreach(static::$createdClient as $callback)
        {
            $callback->bindTo($this)($client);
        }

        return $client;
    }

    protected static $appendOptions = [];

    public static function appendOptions(array|Closure $options)
    {
        static::$appendOptions[] = $options;
    }

    public function getOptions(array $defaults)
    {
        foreach(static::$appendOptions as $options)
        {
            if(is_array($options))
            {
                $defaults = $options + $defaults;
            }
            else
            {
                $defaults = $options->bindTo($this, static::class)($defaults) ?? $defaults;
            }
        }

        return $defaults;
    }

}
