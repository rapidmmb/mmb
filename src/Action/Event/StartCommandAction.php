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
    public function url(...$args)
    {
        return $this->makeLink(...$args);
    }

    /**
     * Create redirect start url from arguments.
     *
     * **This method requires a username in the `'mmb.username'` config.**
     *
     * @param ...$args
     * @return string
     */
    public function makeLink(...$args)
    {
        return $this->makeLinkQuery(
            $this->makeCode(...$args)
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
    public function makeLinkQuery(string $query)
    {
        if (!$username = bot()->info->username) {
            throw new \Exception("makeLinkQuery() method requires a username. Fill it in 'mmb...username' config");
        }

        return "https://t.me/$username" . ($query ? "?" . http_build_query(['start' => $query]) : '');
    }

    /**
     * Create start code from arguments.
     *
     * @param ...$args
     * @return string
     */
    public function makeCode(...$args): string
    {
        $command = $this->getMatcher()->makeQuery(...$args);
        $lower = strtolower($command);

        if ($lower == '/start') {
            return '';
        } elseif (str_starts_with($lower, '/start ')) {
            return substr($command, 7);
        } else {
            return '';
        }
    }

}
