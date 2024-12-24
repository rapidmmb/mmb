<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\WeakSign;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\KeySchema\KeyboardInterface;
use Mmb\Support\KeySchema\KeyInterface;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignKey extends SignWord
{

    /**
     * @var SignLabel<T>
     */
    public SignLabel $label;

    /**
     * @var SignAction<T>
     */
    public SignAction $action;

    public function __construct(WeakSign $sign)
    {
        parent::__construct($sign);
        $this->label = new SignLabel($sign);
        $this->action = new SignAction($sign);
    }


    public int $x = 100;
    public int $y = PHP_INT_MAX;
    public string $group = 'body';
    protected bool|Closure $enabled = true;
    protected ?Closure $key;

    /**
     * Set the key using
     *
     * @param Closure(KeyboardInterface): ?KeyInterface $callback
     * @return T
     */
    public function set(Closure $callback)
    {
        $this->key = $callback;
        return $this->sign;
    }

    /**
     * Modify the key using
     *
     * @param Closure(KeyInterface): KeyInterface $callback
     * @return T
     */
    public function using(Closure $callback)
    {
        $this->listen('using', $callback);
        return $this->sign;
    }

    protected function getEventOptionsOnUsing()
    {
        return [
            'call' => EventCaller::CALL_BUILDER,
        ];
    }

    /**
     * @param int $x
     * @param int $y
     * @param string|null $group
     * @return T
     */
    public function at(int $x, int $y, ?string $group = null)
    {
        $this->x = $x;
        $this->y = $y;
        if (isset($group)) $this->group = $group;

        return $this->sign;
    }

    /**
     * @param string $group
     * @param int|null $x
     * @param int|null $y
     * @return T
     */
    public function in(string $group, ?int $x = null, ?int $y = null)
    {
        if (isset($x)) $this->x = $x;
        if (isset($y)) $this->y = $y;
        $this->group = $group;

        return $this->sign;
    }

    /**
     * @param string|Closure $label
     * @return T
     */
    public function label(string|Closure $label)
    {
        $this->label->set($label);

        return $this->sign;
    }

    /**
     * @param Closure $callback
     * @return T
     */
    public function action(Closure $callback)
    {
        $this->action->set($callback);

        return $this->sign;
    }

    /**
     * @param Closure|null $when
     * @return T
     */
    public function enabled(?Closure $when = null)
    {
        $this->enabled = $when ?? true;
        return $this->sign;
    }

    /**
     * @return T
     */
    public function disabled()
    {
        $this->enabled = false;
        return $this->sign;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function makeKey(KeyboardInterface $base, ...$args): ?KeyInterface
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if (isset($this->key)) {
            $key = $this->call($this->key, $base, ...$args);
        } else {
            $text = $this->label->getNullableLabel(...$args);

            if (is_null($text)) {
                return null;
            }

            $key = $base->makeKey($text, $this->action->callAction(...), $args);
        }

        if ($key) {
            $key = $this->call('using', $key);
        }

        return $key;
    }

}