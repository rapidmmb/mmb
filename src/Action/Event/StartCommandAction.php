<?php

namespace Mmb\Action\Event;

abstract class StartCommandAction extends CommandAction
{

    protected $command = '/start';

    protected $ignoreCase = true;

    /**
     * Create url from arguments
     *
     * @param ...$args
     * @return string
     */
    public static function url(...$args)
    {
        return static::makeLink(...$args);
    }

    /**
     * Create redirect start url from arguments.
     *
     * **This method requires a username in the `'mmb.username'` config.**
     *
     * @param ...$args
     * @return string
     */
    public static function makeLink(...$args)
    {
        return static::makeLinkQuery(
            static::makeCode(...$args)
        );
    }

    /**
     * Create redirect start url from a query.
     *
     * **This method requires a username in the `'mmb.username'` config.**
     *
     * @param string $query
     * @return string
     */
    public static function makeLinkQuery(string $query)
    {
        $username = bot()->info->username;

        if (!$username)
        {
            throw new \Exception("makeLinkQuery() method requires a username. Fill it in 'mmb...username' config");
        }

        if ($query == '')
        {
            return "https://t.me/$username";
        }
        else
        {
            return "https://t.me/$username?start=$query";
        }
    }

    /**
     * Create start code from arguments.
     *
     * @param ...$args
     * @return string
     */
    public static function makeCode(...$args)
    {
        $command = app(static::class)->getMatcher()->makeQuery(...$args);
        $lower = strtolower($command);

        if($lower == '/start')
        {
            return '';
        }
        elseif(str_starts_with($lower, '/start '))
        {
            return substr($command, 7);
        }
        else
        {
            return '';
        }
    }

}
