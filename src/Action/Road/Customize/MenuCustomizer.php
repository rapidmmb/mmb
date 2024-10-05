<?php

namespace Mmb\Action\Road\Customize;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatter;

class MenuCustomizer
{

    protected array $menuSchema = [];

    public function insertKey(string $group, Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
    {
        @$this->menuSchema[$group][] = [0, $key, $x, $y, $name, null];
        return $this;
    }

    public function insertRow(string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null
    )
    {
        @$this->menuSchema[$group][] = [1, $key, 0, $y, $name, $rtl];
        return $this;
    }

    public function insertSchema(
        string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null
    )
    {
        @$this->menuSchema[$group][] = [2, $key, 0, $y, $name, $rtl];
        return $this;
    }

    public function removeKey(string $group, string $name)
    {
        $this->menuSchema[$group] = array_filter(
            $this->menuSchema[$group] ?? [],
            function ($key) use ($name)
            {
                return $key[4] != $name;
            }
        );
        return $this;
    }

    public function moveKey(string $group, string $name, ?int $x, ?int $y)
    {
        $this->menuSchema[$group] = array_map(
            function ($key) use ($name, $x, $y)
            {
                if ($key[4] == $name)
                {
                    if (isset($x))
                        $key[2] = $x;

                    if (isset($y))
                        $key[3] = $y;
                }

                return $key;
            },
            $this->menuSchema[$group] ?? [],
        );
        return $this;
    }

    protected bool $rtl = false;

    public function rtl()
    {
        $this->rtl = true;
        return $this;
    }

    public function ltr()
    {
        $this->rtl = false;
        return $this;
    }

    public function isRtl()
    {
        return $this->rtl;
    }


    protected array $menuActions = [];

    public function insertAction(string $on, Closure $callback, bool $merge = true)
    {
        if ($merge)
            @$this->menuActions[$on][] = $callback;
        else
            $this->menuActions[$on] = [$callback];

        return $this;
    }


    public function init(Station $station, Menu $menu, array $groups)
    {
        foreach ($groups as $group)
        {
            $schema =
                collect($this->menuSchema[$group] ?? [])
                    ->sortBy([3, 2]) // Sort by y, x
                    ->groupBy(3);

            $key = [];
            foreach ($schema as $rowSchema)
            {
                $row = [];
                foreach ($rowSchema as [$type, $builder, $x, $y, , $rtl])
                {
                    $schemaResult = $station->fireSign($builder, $menu);
                    switch ($type)
                    {
                        // Single key
                        case 0:
                            $row[] = $schemaResult;
                            break;

                        // Row
                        case 1:
                            $key[] = $rtl ?? $this->rtl ? array_reverse($schemaResult) : $schemaResult;
                            break;

                        // Schema
                        case 2:
                            array_push($key, ...$rtl ?? $this->rtl ? KeyFormatter::rtl($schemaResult) : $schemaResult);
                            break;
                    }
                }

                if ($row)
                {
                    $key[] = $this->rtl ? array_reverse($row) : $row;
                }
            }

            $menu->schema($key);
        }

        foreach ($this->menuActions as $on => $actions)
        {
            $menu->on(
                $on,
                static function () use ($actions, $station, $menu)
                {
                    $station->fireSign($actions, menu: $menu);
                }
            );
        }
    }

}