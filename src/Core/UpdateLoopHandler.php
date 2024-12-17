<?php

namespace Mmb\Core;

use Amp\CancelledException;
use Amp\DeferredCancellation;
use Closure;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Revolt\EventLoop;
use function Amp\async;
use function Amp\delay;
use function Amp\Future\await;

class UpdateLoopHandler
{

    public function __construct(
        protected Bot      $bot,
        protected ?Closure $callback = null,
        protected ?Closure $received = null,
        protected ?Closure $pass = null,
        protected ?Closure $idle = null,
        protected ?Closure $beforeRun = null,
        protected ?Closure $handling = null,
        protected ?Closure $handled = null,
        protected ?Closure $tagger = null,
        protected int      $timeout = 30,
        protected float    $delay = 0,
        protected int      $maxTry = 5,
    )
    {
    }

    /**
     * Stop the process
     *
     * @return void
     */
    public function stop()
    {
        $this->cancellation->cancel();
        unset($this->tasks);
    }

    private array $tasks;

    public function run()
    {
        throw_if(isset($this->tasks), "Update loop is already ran");

        // Delete webhook
        if (($web = $this->bot->getWebhook()) && $web->url) {
            $this->bot->deleteWebhook();
        }

        if ($this->beforeRun) {
            ($this->beforeRun)();
        }

        $this->tasks = [
            async($this->fetchLoop(...)),
        ];

        return $this;
    }

    public function wait()
    {
        await($this->tasks);
        EventLoop::run(); // todo
        return $this;
    }

    private DeferredCancellation $cancellation;

    protected function fetchLoop()
    {
        $offset = -1;
        $limit = 10;
        $allowedUpdates = null;
        $this->cancellation = new DeferredCancellation();

        try {
            while (true) {
                // Try to get updates
                $updates = $this->fetchUpdates($offset, $limit, $allowedUpdates);

                // Loop and pass to callback
                if ($updates->isNotEmpty()) {
                    foreach ($updates as $update) {
                        $this->handle($update);
                    }

                    $offset = $updates->last()->id + 1;
                }

                if ($this->idle && $updates->isEmpty()) {
                    ($this->idle)();
                }

                if ($this->pass) {
                    ($this->pass)();
                }

                if ($this->delay) {
                    delay($this->delay);
                }
            }
        } catch (CancelledException) {
            return;
        }
    }

    protected function fetchUpdates(int $offset, int $limit, ?array $allowedUpdates)
    {
        $tries = 0;
        while (true) {
            if ($this->cancellation->isCancelled()) {
                throw new CancelledException();
            }

            try {

                return $this->bot->getUpdates(
                    $offset,
                    $limit,
                    $allowedUpdates,
                    $this->timeout,
                    cancellation: $this->cancellation->getCancellation()
                );

            } catch (\Throwable $e) {
                if ($e instanceof CancelledException || ++$tries >= $this->maxTry) {
                    throw $e;
                }

                delay($this->delay);
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
        $tag = $this->tagger ? ($this->tagger)($update) : $update->getChat()?->id;

        if (is_null($tag)) {
            async(fn() => $this->handleNow($update));
            return;
        }

        if (@$this->running[$tag]) {
            $this->queue[$tag] ??= [];
            $this->queue[$tag][] = $update;
            return;
        }

        $this->running[$tag] = true;
        async(fn() => $this->handleNowWithTag($update, $tag));
    }

    protected function handleNow(Update $update)
    {
        if ($this->handling) {
            ($this->handling)($update);
        }

        $update->handle(new Context());

        if ($this->handled) {
            ($this->handled)($update);
        }
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