<?php

namespace Mmb\Core\Traits;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        if($json = $request->json())
        {
            return Update::make($json, $this, true);
        }

        return null;
    }

    /**
     * Get updates from telegram api
     *
     * @param       $offset
     * @param       $limit
     * @param       $allowedUpdates
     * @param array $args
     * @param       ...$namedArgs
     * @return Collection
     */
    public function getUpdates($offset = null, $limit = null, $allowedUpdates = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'offset'         => $offset,
                'limit'          => $limit,
                'allowedUpdates' => $allowedUpdates,
            ],
            $args + $namedArgs
        );

        if($updates = $this->request('getUpdates', $args))
        {
            return collect($updates)
                ->map(fn($update) => $this->makeData(Update::class, $update));
        }

        return collect();
    }

    /**
     * Loop updates
     *
     * @param Closure|null $callback
     * @param              $delay
     * @return never
     */
    public function loopUpdates(Closure $callback = null, Closure $received = null, $delay = 100)
    {
        // Delete webhook
        if(($web = $this->getWebhook()) && $web->url)
        {
            $this->deleteWebhook();
        }

        // Default callbacks
        $callback ??= fn(Update $update) => $update->handle();

        $offset = -1;
        $limit = 10;
        $allowedUpdates = null;

        while(true)
        {
            // Try to get updates
            $updates = retry(5, fn() => $this->getUpdates($offset, $limit, $allowedUpdates), $delay);

            // Loop and pass to callback
            if($updates->isNotEmpty())
            {
                foreach($updates as $update)
                {
                    // TODO: try and catch to pass into the debugger
                    if($received)
                    {
                        $received($update);
                    }

                    $callback($update);
                }

                $offset = $updates->last()->id + 1;
            }

            usleep($delay * 1000);
        }
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
