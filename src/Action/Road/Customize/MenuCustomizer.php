<?php

namespace Mmb\Action\Road\Customize;

use Closure;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\WeakSign;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatter;

class MenuCustomizer
{
    use Concerns\SchemaCustomizes;

    public function __construct(
        protected WeakSign $sign,
    )
    {
    }

    public function init(Station $station, Menu $menu, array $groups)
    {
        $this->initKeyboards($menu, $groups);

        foreach ($this->actions as $on => $actions) {
            $menu->on(
                $on,
                function () use ($station, $actions, $menu) {
                    $station->fireSignAs($this->sign, $actions, menu: $menu);
                },
            );
        }
    }

}