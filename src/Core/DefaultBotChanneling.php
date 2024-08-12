<?php

namespace Mmb\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mmb\Http\Controllers\WebhookController;

/**
 * Default bot channeling
 *
 * This channeling is default mode.
 * Your bots in the config->mmb->channels are known as your robots in this project.
 * The hookToken is the suffix for webhook address and separates the response and update handling.
 * You can choose many handlers to make many bots in the one project.
 */
class DefaultBotChanneling extends BotChanneling
{

    /**
     * Define webhook route with hook token
     *
     * @return void
     */
    public function defineRoutes()
    {
        Route::post('bot/{hookToken}', [WebhookController::class, 'update'])->name('mmb.webhook');
    }

    /**
     * Validate and handle update
     *
     * @param string  $hookToken
     * @param Request $request
     * @return mixed
     */
    public function onRoute(string $hookToken, Request $request)
    {
        // Invalid hook token
        if(!$this->findAndBind($hookToken))
        {
            return '';
        }

        // Handle update
        return $this->handleUpdate($request);
    }

    /**
     * Get bot webhook url
     *
     * @param InternalBotInfo $info
     * @return string|null
     */
    public function getWebhookUrl(InternalBotInfo $info)
    {
        if (isset($info->configName) && $args = @$this->args[$info->configName])
        {
            return route('mmb.webhook', ['hookToken' => $args['hookToken']]);
        }

        return null;
    }

}
