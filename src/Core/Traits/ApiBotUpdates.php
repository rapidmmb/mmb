<?php

namespace Mmb\Core\Traits;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mmb\Core\UpdateLoopHandler;
use Mmb\Core\Updates\Update;
use Mmb\Core\Updates\Webhooks\WebhookInfo;

trait ApiBotUpdates
{

    /**
     * Get current update from telegram request
     *
     * @return ?Update
     */
    public function getUpdate()
    {
        return $this->getUpdateOf(request());
    }

    /**
     * Get update from telegram request
     *
     * @param Request $request
     * @return ?Update
     */
    public function getUpdateOf(Request $request)
    {
        if ($json = $request->json())
        {
            return Update::make($json, $this, true);
        }

        return null;
    }

    /**
     * Get updates from telegram api
     *
     * @param mixed $offset
     * @param mixed $limit
     * @param mixed $allowedUpdates
     * @param int   $timeout
     * @param array $args
     * @param       ...$namedArgs
     * @return Collection
     */
    public function getUpdates(
        $offset = null, $limit = null, $allowedUpdates = null, $timeout = 0, array $args = [], ...$namedArgs
    )
    {
        $args = $this->mergeMultiple(
            [
                'offset'         => $offset,
                'limit'          => $limit,
                'allowedUpdates' => $allowedUpdates,
                'timeout'        => $timeout,
            ],
            $args + $namedArgs
        );

        if ($updates = $this->request('getUpdates', $args))
        {
            return collect($updates)
                ->map(fn ($update) => $this->makeData(Update::class, $update));
        }

        return collect();
    }

    /**
     * Loop updates
     *
     * @param Closure|null $callback
     * @param Closure|null $received
     * @param Closure|null $pass
     * @param int          $timeout
     * @param float        $delay
     * @return void
     */
    public function loopUpdates(
        Closure $callback = null,
        Closure $received = null,
        Closure $pass = null,
        int     $timeout = 30,
        float   $delay = 0,
    )
    {
        (new UpdateLoopHandler(
            $this,
            callback: $callback,
            received: $received,
            pass: $pass,
            timeout: $timeout,
            delay: $delay,
        ))->run()->wait();
    }

    /**
     * Get webhook info
     *
     * @return WebhookInfo|null
     */
    public function getWebhookInfo(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            WebhookInfo::class,
            $this->request('getWebhookInfo', $args + $namedArgs),
        );
    }

    /**
     * Get webhook info
     *
     * Alias to {@see getWebhookInfo}
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return WebhookInfo|null
     */
    public function getWebhook(array $args = [], ...$namedArgs)
    {
        return $this->getWebhookInfo($args, ...$namedArgs);
    }

    /**
     * Delete webhook
     *
     * @param array $args
     * @param mixed ...$namedArgs
     * @return bool
     */
    public function deleteWebhook(array $args = [], ...$namedArgs)
    {
        return $this->request('deleteWebhook', $args + $namedArgs);
    }

    /**
     * Set webhook
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return bool
     */
    public function setWebhook(array $args = [], ...$namedArgs)
    {
        return $this->request('setWebhook', $args + $namedArgs);
    }

    /**
     * Set webhook url to current application webhook url
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return bool
     */
    public function setMyWebhook(array $args = [], ...$namedArgs)
    {
        return $this->setWebhook($args + $namedArgs + ['url' => $this->info->getWebhookUrl()]);
    }

}
