<?php

namespace Mmb\Action\Road\Station\Concerns;

use Closure;
use Illuminate\Support\Str;
use Mmb\Action\Road\Customize\MenuCustomizer;
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
    public function useMenu(Closure $callback)
    {
        $callback($this->getMenuCustomizer());
        return $this;
    }

    /**
     * Insert a key
     *
     * @param string                  $group
     * @param Closure(Menu): ?MenuKey $key
     * @param string|null             $name
     * @param int                     $x
     * @param int                     $y
     * @return $this
     */
    public function insertKey(string $group, Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
    {
        $this->getMenuCustomizer()->insertKey($group, $key, $name, $x, $y);
        return $this;
    }

    /**
     * Insert a key row
     *
     * @param string                         $group
     * @param Closure(Menu): array<?MenuKey> $key
     * @param string|null                    $name
     * @param int                            $y
     * @param bool|null                      $rtl
     * @return $this
     */
    public function insertRow(string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null)
    {
        $this->getMenuCustomizer()->insertRow($group, $key, $name, $y, $rtl);
        return $this;
    }

    /**
     * Insert a key schema
     *
     * @param string                                $group
     * @param Closure(Menu): array<array<?MenuKey>> $key
     * @param string|null                           $name
     * @param int                                   $y
     * @param bool|null                             $rtl
     * @return $this
     */
    public function insertSchema(string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null)
    {
        $this->getMenuCustomizer()->insertSchema($group, $key, $name, $y, $rtl);
        return $this;
    }

    /**
     * Remove key by name
     *
     * @param string $group
     * @param string $name
     * @return $this
     */
    public function removeKey(string $group, string $name)
    {
        $this->getMenuCustomizer()->removeKey($group, $name);
        return $this;
    }

    /**
     * Move key by name
     *
     * @param string   $group
     * @param string   $name
     * @param int|null $x
     * @param int|null $y
     * @return $this
     */
    public function moveKey(string $group, string $name, ?int $x, ?int $y)
    {
        $this->getMenuCustomizer()->moveKey($group, $name, $x, $y);
        return $this;
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
     * @param string               $on
     * @param Closure(Menu): mixed $callback
     * @param bool                 $merge
     * @return $this
     */
    public function insertAction(string $on, Closure $callback, bool $merge = true)
    {
        $this->getMenuCustomizer()->insertAction($on, $callback, $merge);
        return $this;
    }

}