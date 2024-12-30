<?php

namespace Mmb\Action\Road\Station\Concerns;

use Closure;
use Illuminate\Support\Str;
use Mmb\Action\Road\Customize\MenuCustomizer;
use Mmb\Action\Road\Station\Words\SignAction;
use Mmb\Action\Road\Station\Words\SignKey;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\MenuKey;

trait SignWithMenuCustomizing
{

    protected function bootHasMenuCustomizing()
    {
        match ($this->road->getRtl())
        {
            true => $this->rtl(),
            false => $this->ltr(),
            default => null,
        };
    }

    private MenuCustomizer $_menuCustomizer;

    public function getMenuCustomizer() : MenuCustomizer
    {
        return $this->_menuCustomizer ??= new MenuCustomizer($this);
    }

    /**
     * Get menu customizer in callback
     *
     * @param Closure $callback
     * @return $this
     */
    public function tapMenu(Closure $callback)
    {
        $callback($this->getMenuCustomizer());
        return $this;
    }

    public function addKey(SignKey $key)
    {
        $this->getMenuCustomizer()->addKey($key);
    }

    /**
     * Set rtl the keyboard
     *
     * @return $this
     */
    public function rtl()
    {
        $this->getMenuCustomizer()->rtl();
        return $this;
    }

    /**
     * Set ltr the keyboard
     *
     * @return $this
     */
    public function ltr()
    {
        $this->getMenuCustomizer()->ltr();
        return $this;
    }

    /**
     * Insert an action
     *
     * @param string $on
     * @param SignAction $action
     * @param bool $merge
     * @return $this
     */
    public function insertAction(string $on, SignAction $action, bool $merge = true)
    {
        $this->getMenuCustomizer()->addAction($on, $action, $merge);
        return $this;
    }

}