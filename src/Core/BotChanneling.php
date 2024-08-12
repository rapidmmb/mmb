<?php

namespace Mmb\Core;

use Exception;
use Illuminate\Http\Request;
use Mmb\Core\Updates\Update;

abstract class BotChanneling
{

    public function __construct(
        public array $args,
    )
    {
    }

    /**
     * Define webhook routes
     *
     * @return void
     */
    public function defineRoutes()
    {
    }

    /**
     * Find robot name by hook token (or null if fails)
     *
     * @param string $hookToken
     * @return string|null
     */
    public function findByHookToken(string $hookToken)
    {
        foreach($this->args as $name => $info)
        {
            if($hookToken == $info['hookToken'] ?? $name)
            {
                return $name;
            }
        }

        return null;
    }

    /**
     * Find and get the Bot object by hook name (or null if fails)
     *
     * @param string $hookToken
     * @return Bot|null
     */
    public function findAndGet(string $hookToken)
    {
        $name = $this->findByHookToken($hookToken);

        if($name === null)
        {
            return null;
        }

        return $this->getBot($name, $hookToken);
    }

    /**
     * Find, get, and bind the Bot object using hook name (return null if fails)
     *
     * @param string $hookToken
     * @return Bot|null
     */
    public function findAndBind(string $hookToken)
    {
        $bot = $this->findAndGet($hookToken);

        if($bot === null)
        {
            return null;
        }

        $this->defaultBot = $bot;
        return $bot;
    }

    /**
     * Create Update object from request value
     *
     * @param Request $request
     * @return Update|null
     */
    public function makeUpdate(Request $request)
    {
        return Update::make($request->all(), app(Bot::class), true);
    }

    /**
     * Get Bot object from name
     *
     * @param string $name
     * @param ?string $hookToken
     * @return Bot
     */
    public function getBot(string $name, ?string $hookToken)
    {
        if (array_key_exists($name, $this->args))
        {
            $data = $this->args[$name];
            $bot = new Bot(new InternalBotInfo(
                token: $data['token'],
                username: @$data['username'],
                guardName: $data['guard'] ?? $this->getDefaultGuard(),
                configName: $name,
            ));

            $this->registerBot($bot, $data);

            return $bot;
        }

        throw new \InvalidArgumentException("Bot [$name] is not defined");
    }

    /**
     * Get default guard name
     *
     * @return ?string
     */
    public function getDefaultGuard()
    {
        return config('mmb.default_guard');
    }

    /**
     * Default bot value
     *
     * @var Bot|null
     */
    protected ?Bot $defaultBot;

    /**
     * Get default Bot object
     *
     * @return Bot|null
     */
    public function getDefaultBot()
    {
        if (isset($this->defaultBot))
        {
            return $this->defaultBot;
        }

        return $this->getBot('default', null);
    }

    /**
     * Register the Bot object data, like handlers
     *
     * @param Bot   $bot
     * @param array $data
     * @return void
     */
    protected function registerBot(Bot $bot, array $data)
    {
        $bot->registerHandlers($data['handlers'] ?? []);
    }

    /**
     * Get bot webhook url
     *
     * @return ?string
     */
    public abstract function getWebhookUrl(InternalBotInfo $info);

    /**
     * Handle update by request
     *
     * @param Request $request
     * @return string
     * @throws Exception
     */
    protected function handleUpdate(Request $request)
    {
        try
        {
            $this->makeUpdate($request)?->handle();
            return '';
        }
        catch(Exception $e)
        {
            return $this->handleException($e);
        }
    }

    /**
     * Handle thrown exception
     *
     * @param Exception $e
     * @return mixed
     * @throws Exception
     */
    protected function handleException(Exception $e)
    {
        throw $e;
    }

}
