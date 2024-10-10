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
        foreach ($groups as $group)
        {
            $menu->schema($this->fetchSchema($station, $group, $menu));
        }

        foreach ($this->actions as $on => $actions)
        {
            $menu->on(
                $on,
                static function () use ($actions, $station, $menu)
                {
                    $station->fireSignAs($this->sign, $actions, menu: $menu);
                }
            );
        }
    }

}