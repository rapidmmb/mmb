<?php

namespace Mmb\Action\Road\Customize\Concerns;

use Mmb\Action\Road\Station;
use Mmb\Support\Format\KeyFormatter;
use Mmb\Support\KeySchema\KeyboardInterface;

trait SchemaCustomizes
{

    /**
     * @var Station\Words\SignKey[]
     */
    protected array $keys = [];

    public function addKey(Station\Words\SignKey $key)
    {
        @$this->keys[] = $key;
        return $this;
    }

// todo remove
//
//    public function insertKey(string $group, Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
//    {
//        @$this->schemas[$group][] = [0, $key, $x, $y, $name, null];
//        return $this;
//    }
//
//    public function insertRow(string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null,
//    )
//    {
//        @$this->schemas[$group][] = [1, $key, 0, $y, $name, $rtl];
//        return $this;
//    }
//
//    public function insertSchema(
//        string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null,
//    )
//    {
//        @$this->schemas[$group][] = [2, $key, 0, $y, $name, $rtl];
//        return $this;
//    }
//
//    public function removeKey(string $group, string $name)
//    {
//        $this->schemas[$group] = array_filter(
//            $this->schemas[$group] ?? [],
//            function ($key) use ($name) {
//                return $key[4] != $name;
//            },
//        );
//        return $this;
//    }
//
//    public function moveKey(string $group, string $name, ?int $x, ?int $y)
//    {
//        $this->schemas[$group] = array_map(
//            function ($key) use ($name, $x, $y) {
//                if ($key[4] == $name) {
//                    if (isset($x))
//                        $key[2] = $x;
//
//                    if (isset($y))
//                        $key[3] = $y;
//                }
//
//                return $key;
//            },
//            $this->schemas[$group] ?? [],
//        );
//        return $this;
//    }

    protected ?bool $rtl = null;

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

    public function isRtl(): ?bool
    {
        return $this->rtl ?? $this->sign->road->getRtl();
    }


    public function initKeyboards(KeyboardInterface $base, array $groups)
    {
        foreach ($groups as $group) {
            $this->applySchema($base, $group);
        }
    }

    public function applySchema(KeyboardInterface $base, string $group): void
    {
        $base->schema(
            collect($this->keys)
                ->where('group', $group)
                ->filter(fn(Station\Words\SignKey $key) => $key->isEnabled())
                ->sortBy(['y', 'x'])
                ->groupBy('y')
                ->map->map(function (Station\Words\SignKey $key) use ($base) {
                    return $key->makeKey($base);
                })
                ->when($this->isRtl(), fn($collection) => $collection->map->reverse())
                ->toArray(),
        );
    }

    /**
     * @var array<string, Station\Words\SignAction[]>
     */
    protected array $actions = [];

    public function addAction(string $on, Station\Words\SignAction $action, bool $merge = true)
    {
        if ($merge)
            @$this->actions[$on][] = $action;
        else
            $this->actions[$on] = [$action];

        return $this;
    }

}