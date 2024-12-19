<?php

namespace Mmb\Action\Road\Station\Concerns;

use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Road\Station;
use Closure;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\MenuKey;

/**
 * @method $this itemKey(Closure|false $callback)
 * @method $this itemKeyAction(Closure $action)
 * @method $this itemKeyLabel(Closure $callback)
 * @method $this itemKeyLabelUsing(Closure $callback)
 * @method $this itemKeyLabelPrefix(string|Closure $string)
 * @method $this itemKeyLabelSuffix(string|Closure $string)
 */
trait SignWithItems
{

    public Station\Words\SignKey $itemKey;

    protected function bootSignWithItems()
    {
        $this->itemKey = new Station\Words\SignKey($this->sign);
        $this->itemKey->label(function ($record) {
            if ($record instanceof Model)
            {
                return $record->name ?? $record->title ?? $record->getKey();
            }

            return object_get($record, 'name') ?? object_get($record, 'title') ?? throw_if(true, 'Unresolved item label');
        });
    }

    /**
     * Get an item key label
     *
     * @param Station $station
     * @param         $record
     * @return string
     */
    public function getItemKeyLabel(Station $station, $record) : string
    {
        return $this->getDefinedLabel($station, 'itemKeyLabel', $record);
    }

    /**
     * Get an item key label
     *
     * @param Station $station
     * @param Menu    $menu
     * @param         $record
     * @return ?MenuKey
     */
    public function getItemKey(Station $station, Menu $menu, $record) : ?MenuKey
    {
        return $this->getDefinedDynamicKey($station, 'itemKey', $menu, $record);
    }

    /**
     * Get an item key action
     *
     * @param Station $station
     * @param         $record
     * @return Closure
     */
    public function getItemKeyAction(Station $station, $record) : Closure
    {
        return fn () => $this->fireBy($station, 'itemKeyAction', $record);
    }

    protected function onItemKey(Menu $menu, $record, Station $station) : ?MenuKey
    {
        return $menu->key(
            $this->getItemKeyLabel($station, $record),
            $this->getItemKeyAction($station, $record),
        );
    }

    protected function onItemKeyLabel($record) : string
    {
        if ($record instanceof Model)
        {
            return $record->name ?? $record->title ?? $record->getKey();
        }

        return object_get($record, 'name') ?? object_get($record, 'title') ?? throw_if(true, 'Unresolved item label');
    }

    protected function onItemKeyAction($record)
    {

    }

}