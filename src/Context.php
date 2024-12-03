<?php

namespace Mmb;

use ArrayAccess;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Update;

/**
 * @property ?Update $update
 * @property ?Bot $bot
 */
class Context implements ArrayAccess
{

    protected const ALIASES = [
        'update' => Update::class,
        'bot' => Bot::class,
    ];

    protected array $data = [];

    /**
     * Put a value in the context
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        if (array_key_exists($key, static::ALIASES)) {
            $key = static::ALIASES[$key];
        }

        $this->data[$key] = $value;
    }

    /**
     * Get a value from context
     *
     * @template T
     * @param string|class-string<T> $key
     * @param $default
     * @return T|mixed
     */
    public function get(string $key, $default = null): mixed
    {
        if (array_key_exists($key, static::ALIASES)) {
            $key = static::ALIASES[$key];
        }

        if (!array_key_exists($key, $this->data)) {
            return value($default);
        }

        return $this->data[$key];
    }

    /**
     * Checks a key is defined in the context
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (array_key_exists($key, static::ALIASES)) {
            $key = static::ALIASES[$key];
        }

        return array_key_exists($key, $this->data);
    }

    /**
     * Forget a key from context
     *
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        if (array_key_exists($key, static::ALIASES)) {
            $key = static::ALIASES[$key];
        }

        unset($this->data[$key]);
    }


    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value): void
    {
        $this->put($name, $value);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string)$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->put((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->forget((string)$offset);
    }
}