<?php

namespace Mmb\Action\Section;

use Closure;

class MenuKeyGroup
{

    public function __construct(
        public Menu   $menu,
        public        $key,
        public string $name = 'main',
        public bool   $fixed = false,
        public bool   $exclude = false,
    )
    {
    }

    public function normalizeKey(bool $storable = false, bool $isInline = false)
    {
        $map = value($this->key);
        $resultKey = [];
        $resultActions = [];

        if(is_iterable($map))
        {
            $map = iterator_to_array($map);
        }

        if($map === null)
        {
            return [];
        }

        if(!is_array($map))
        {
            throw new \TypeError("Keyboard should be array, given " . gettype($map));
        }

        foreach($map as $rowKey => $row)
        {
            $row = value($row);
            if($row === null)
            {
                continue;
            }

            if(is_iterable($row))
            {
                $row = iterator_to_array($row);
            }

            if(!is_array($row))
            {
                throw new \TypeError("Keyboard row should be array at [$rowKey], given " . gettype($map));
            }

            $keyboardRow = [];
            foreach($row as $columnKey => $column)
            {
                $column = value($column);
                if($column === null)
                {
                    continue;
                }

                if(is_iterable($column))
                {
                    $column = iterator_to_array($column);
                }

                if($column instanceof MenuKey)
                {
                    if(!$column->isDisplayed())
                    {
                        continue;
                    }

                    $attrs = $column->getAttributes();
                    $action = $column->isIncluded() ? $column->getAction() : null;
                    $actionKey = $column->getActionKey($isInline);
                    $visible = $column->isVisible();
                }
                elseif(is_array($column))
                {
                    $attrs = $column;
                    $action = null;
                    $actionKey = null;
                    $visible = true;
                }
                else
                {
                    throw new \TypeError(
                        "Keyboard column should be array or MenuKey at [$rowKey][$columnKey], given " . gettype($column)
                    );
                }

                if(!is_null($action))
                {
                    if($storable && !$action->isStorable())
                    {
                        throw new \TypeError(
                            "Keyboard action with Closure value is not available for storable menu"
                        );
                    }

                    $resultActions[$actionKey] = $action;
                }

                if($visible)
                {
                    $keyboardRow[] = $attrs;
                }
            }

            if($keyboardRow)
            {
                $resultKey[] = $keyboardRow;
            }
        }

        return [$resultKey, $resultActions];
    }

}
