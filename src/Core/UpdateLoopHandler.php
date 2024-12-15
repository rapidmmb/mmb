<?php

namespace Mmb\Core;

use Closure;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Revolt\EventLoop;
use function Amp\async;
use function Amp\Future\awaitAll;

class UpdateLoopHandler
{

    public function __construct(
        protected Bot      $bot,
        protected ?Closure $callback = null,
        protected ?Closure $received = null,
        protected ?Closure $pass = null,
        protected int      $timeout = 30,
        protected float    $delay = 0,
    )
    {
    }

    private array $tasks;

    public function run()
    {
        throw_if(isset($this->tasks), "Update loop is already ran");

        // Delete webhook
        if (($web = $this->bot->getWebhook()) && $web->url) {
            $this->bot->deleteWebhook();
        }

        $this->tasks = [

            async($this->fetchLoop(...)),

        ];

        return $this;
    }

    public function wait()
    {
        awaitAll($this->tasks);
        EventLoop::run(); // todo
        return $this;
    }

    protected function fetchLoop()
    {
        $offset = -1;
        $limit = 10;
        $allowedUpdates = null;

        while (true) {
            // Try to get updates
            $updates = retry(5, fn() => $this->bot->getUpdates($offset, $limit, $allowedUpdates, $this->timeout), $this->delay);

            // Loop and pass to callback
            if ($updates->isNotEmpty()) {
                foreach ($updates as $update) {
                    $this->handle($update);
                }

                $offset = $updates->last()->id + 1;
            }

            if ($this->pass) {
                ($this->pass)();
            }

            if ($this->delay) {
                usleep($this->delay * 1000);
            }
        }
    }

    protected function handle(Update $update)
    {
        // TODO: try and catch to pass into the debugger
        if ($this->received) {
            ($this->received)($update);
        }

        if ($this->callback) {
            ($this->callback)($update);
        } else {
            $this->handleDefault($update);
        }
    }

    protected array $queue = [];

    protected array $running = [];

    protected function handleDefault(Update $update)
    {
        $chatId = $update->getChat()?->id;

        if (is_null($chatId)) {
            async(fn () => $this->handleNow($update));
            return;
        }

        if (@$this->running[$chatId]) {
            $this->queue[$chatId] ??= [];
            $this->queue[$chatId][] = $update;
            return;
        }

        async(fn () => $this->handleNowWithTag($update, $chatId));
    }

    protected function handleNow(Update $update)
    {
        $update->handle(new Context());
    }

    protected function handleNowWithTag(Update $update, int|string $tag)
    {
        $this->running[$tag] = true;

        $this->handleNow($update);

        while (@$this->queue[$tag]) {
            $this->handleNow(
                array_shift($this->queue[$tag])
            );
        }

        $this->running[$tag] = false;
    }

}