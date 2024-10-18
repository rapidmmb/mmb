<?php

namespace Mmb\Action\Service;

use Closure;
use Mmb\Core\Updates\Update;
use Mmb\Support\Pov\POV;

class Service
{

    public readonly Update $update;

    public function __construct(
        ?Update $update = null,
    )
    {
        $this->update = $update ?? app(Update::class);
    }

    /**
     * @return ServiceProxy<static>|static
     */
    public static function make(?Update $update = null)
    {
        return new ServiceProxy(new static($update));
    }


    /**
     * @param                     $user
     * @param Closure|string|null $callback
     * @param                     ...$args
     * @return POV|mixed
     * @throws \Throwable
     */
    protected function forUser($user, null|Closure|string $callback = null, ...$args)
    {
        return pov()
            ->user($user)
            ->when($callback)
            ->run(fn () => $callback instanceof Closure ? $callback(...$args) : $this->$callback(...$args));
    }

    /**
     * @param                $user
     * @param string         $section
     * @param string         $name
     * @param mixed          ...$args
     * @return ServiceEvent
     * @throws \Throwable
     */
    protected function notify($user, string $section, string $name, ...$args)
    {
        $event = new ServiceEvent();

        $callback = fn () => $section::invokeDynamics('notify' . $name, $args, ['event' => $event]);
        $result = $user ? $this->forUser($user, $callback) : $callback();

        if (isset($result))
        {
            $event->result = $result;
        }

        return $event;
    }

    /**
     * @param                $user
     * @param string         $section
     * @param string         $name
     * @param mixed          ...$args
     * @return ?ServiceEvent
     */
    protected function tryNotify($user, string $section, string $name, ...$args)
    {
        try
        {
            return $this->notify($user, $section, $name, ...$args);
        }
        catch (\Throwable)
        {
            return null;
        }
    }

    /**
     * @param string $section
     * @param string $name
     * @param        ...$args
     * @return ServiceEvent
     * @throws \Throwable
     */
    protected function notifyMe(string $section, string $name, ...$args)
    {
        return $this->notify(null, $section, $name, ...$args);
    }

    /**
     * @param string $section
     * @param string $name
     * @param        ...$args
     * @return ServiceEvent|null
     */
    protected function tryNotifyMe(string $section, string $name, ...$args)
    {
        return $this->tryNotify(null, $section, $name, ...$args);
    }

    /**
     * Fail the service and throw an error
     *
     * @param     $message
     * @param int $code
     * @return mixed
     * @throws ServiceFailed
     */
    protected function error($message, int $code = 0)
    {
        throw new ServiceFailed($code, $message);
    }

}