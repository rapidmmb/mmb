<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\Sign;

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


    protected int $x = 0;
    protected int $y = 0;
    protected string $group = 'main';
    protected bool|Closure $enabled = true;

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

}