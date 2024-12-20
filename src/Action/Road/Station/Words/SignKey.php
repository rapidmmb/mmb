<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\Sign;
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

    public function __construct(Sign $sign)
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
     * @return $this
     */
    public function set(Closure $callback)
    {
        $this->key = $callback;
        return $this;
    }

    /**
     * Modify the key using
     *
     * @param Closure(KeyInterface): KeyInterface $callback
     * @return $this
     */
    public function using(Closure $callback)
    {
        $this->listen('using', $callback);
        return $this;
    }

    protected function getEventOptionsOnUsing()
    {
        return [
            EventCaller::CALL_BUILDER,
        ];
    }

    public function at(int $x, int $y, ?string $group = null)
    {
        $this->x = $x;
        $this->y = $y;
        if (isset($group)) $this->group = $group;

        return $this->sign;
    }

    public function in(string $group, ?int $x = null, ?int $y = null)
    {
        if (isset($x)) $this->x = $x;
        if (isset($y)) $this->y = $y;
        $this->group = $group;

        return $this->sign;
    }

    public function label(string|Closure $label)
    {
        $this->label->set($label);

        return $this->sign;
    }

    public function action(Closure $callback)
    {
        $this->action->set($callback);

        return $this->sign;
    }

    public function enabled(?Closure $when = null)
    {
        $this->enabled = $when ?? true;
        return $this->sign;
    }

    public function disabled()
    {
        $this->enabled = false;
        return $this->sign;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function makeKey(KeyboardInterface $base): ?KeyInterface
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if (isset($this->key)) {
            $key = $this->call($this->key, $base);
        } else {
            $text = $this->label->getNullableLabel();

            if (is_null($text)) {
                return null;
            }

            $key = $base->makeKey($text, $this->action->callAction(...), []);
        }

        if ($key) {
            $key = $this->call('using', $key);
        }

        return $key;
    }

}