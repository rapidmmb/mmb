<?php

namespace Mmb;

use ArrayAccess;
use Mmb\Action\Memory\StepFactory;
use Mmb\Context\Contracts\ContextAttribute;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Callbacks\CallbackQuery;
use Mmb\Core\Updates\Inlines\InlineQuery;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\Contracts\Stepper;

/**
 * @property ?Bot $bot
 * @property ?Update $update
 * @property-read ?Message $message
 * @property-read ?CallbackQuery $callbackQuery
 * @property-read ?InlineQuery $inlineQuery
 * @property StepFactory $stepFactory
 * @property-read ?Stepper $stepper
 */
class Context implements ArrayAccess
{

    public function __construct()
    {
        $this->instance(new StepFactory($this));
    }

    public function copy(): static
    {
        $copy = clone $this;

//        if ($object = $this->get(StepFactory::class)) {
//            $copy->instance(clone $object);
//        }

        return $copy;
    }

    protected static array $aliases = [
        'bot' => Bot::class,
        'update' => Update::class,
        'stepFactory' => StepFactory::class,
    ];

    /**
     * Define an alias
     *
     * @param string $original
     * @param string $alias
     * @return void
     */
    public static function defineAlias(string $original, string $alias): void
    {
        static::$aliases[$alias] = $original;
    }


    /**
     * Array of attributes
     *
     * @var array<string, ContextAttribute>
     */
    protected static array $attributes = [];

    /**
     * Define an attribute
     *
     * @param string $key
     * @param ContextAttribute $attribute
     * @return void
     */
    public static function define(string $key, ContextAttribute $attribute): void
    {
        static::$attributes[$key] = $attribute;
    }


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
        if (array_key_exists($key, static::$aliases)) {
            $key = static::$aliases[$key];
        }

        if (array_key_exists($key, static::$attributes)) {
            static::$attributes[$key]->put($this, $key, $value);
            return;
        }

        if (method_exists($this, 'set' . $key)) {
            $this->{'set' . $key}($value);
            return;
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
        if (array_key_exists($key, static::$aliases)) {
            $key = static::$aliases[$key];
        }

        if (array_key_exists($key, static::$attributes)) {
            return static::$attributes[$key]->get($this, $key, $default);
        }

        if ($key && method_exists($this, 'get' . $key)) {
            return $this->{'get' . $key}($default);
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
        if (array_key_exists($key, static::$aliases)) {
            $key = static::$aliases[$key];
        }

        if (array_key_exists($key, static::$attributes)) {
            return static::$attributes[$key]->has($this, $key);
        }

        return array_key_exists($key, $this->data) || ($key && method_exists($this, 'get' . $key));
    }

    /**
     * Forget a key from context
     *
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        if (array_key_exists($key, static::$aliases)) {
            $key = static::$aliases[$key];
        }

        if (array_key_exists($key, static::$attributes)) {
            static::$attributes[$key]->forget($this, $key);
            return;
        }

        if ($key && method_exists($this, 'set' . $key)) {
            $this->{'set' . $key}(null);
            return;
        }

        unset($this->data[$key]);
    }

    /**
     * Set the class instance to the object
     *
     * @param object $object
     * @return void
     */
    public function instance(object $object): void
    {
        $this->put(get_class($object), $object);
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


    protected function getMessage(): ?Message
    {
        return $this->get(Update::class)?->message;
    }

    protected function getCallbackQuery(): ?CallbackQuery
    {
        return $this->get(Update::class)?->callbackQuery;
    }

    protected function getInlineQuery(): ?InlineQuery
    {
        return $this->get(Update::class)?->inlineQuery;
    }

    protected function getStepper(): ?Stepper
    {
        return $this->get(StepFactory::class)?->getModel();
    }

}