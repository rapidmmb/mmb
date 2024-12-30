<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Action\Road\Station\ItemSign;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignItems extends SignWord
{

    /**
     * @var SignKey<T>
     */
    public SignKey $key;

    /**
     * @param Closure $callback
     * @return T
     */
    public function label(Closure $callback)
    {
        $this->key->label($callback);
        return $this->sign;
    }

    /**
     * Define items separately
     *
     * @param Closure(ItemSign $item): void $callback
     * @return T
     */
    public function each(Closure $callback)
    {
        $this->listen('each', $callback);
        return $this->sign;
    }

    /**
     * @param Closure(object $record): bool $condition
     * @return T
     */
    public function visible(Closure $condition)
    {
        return $this->each(function (ItemSign $item) use ($condition) {
            $item->visible($condition($item->record));
        });
    }

    /**
     * @param Closure(object $record): bool $condition
     * @return T
     */
    public function hidden(Closure $condition)
    {
        return $this->each(function (ItemSign $item) use ($condition) {
            $item->hidden($condition($item->record));
        });
    }


    /**
     * @param object[] $records
     * @return ItemSign[]
     */
    public function getItems(array $records): array
    {
        $items = [];

        foreach ($records as $record) {
            $item = new ItemSign($this->road, $this->sign, $record);
            $item->key = clone $this->key;

            $this->fire('each', $item);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param object[] $records
     * @return ItemSign[]
     */
    public function getVisibleItems(array $records): array
    {
        return collect($this->getItems($records))
            ->where('visible', true)
            ->all();
    }

}