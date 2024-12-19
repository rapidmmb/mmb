<?php

namespace Mmb\Action\Road\Customize\Concerns;

use Closure;
use Mmb\Action\Road\Station;
use Mmb\Support\Format\KeyFormatter;

trait SchemaCustomizes
{

    protected array $schemas = [];

    public function getAllSchemas()
    {
        return $this->schemas;
    }

    public function insertKey(string $group, Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
    {
        @$this->schemas[$group][] = [0, $key, $x, $y, $name, null];
        return $this;
    }

    public function insertRow(string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null,
    )
    {
        @$this->schemas[$group][] = [1, $key, 0, $y, $name, $rtl];
        return $this;
    }

    public function insertSchema(
        string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null,
    )
    {
        @$this->schemas[$group][] = [2, $key, 0, $y, $name, $rtl];
        return $this;
    }

    public function removeKey(string $group, string $name)
    {
        $this->schemas[$group] = array_filter(
            $this->schemas[$group] ?? [],
            function ($key) use ($name) {
                return $key[4] != $name;
            },
        );
        return $this;
    }

    public function moveKey(string $group, string $name, ?int $x, ?int $y)
    {
        $this->schemas[$group] = array_map(
            function ($key) use ($name, $x, $y) {
                if ($key[4] == $name) {
                    if (isset($x))
                        $key[2] = $x;

                    if (isset($y))
                        $key[3] = $y;
                }

                return $key;
            },
            $this->schemas[$group] ?? [],
        );
        return $this;
    }

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

    protected function fetchSchema(Station $station, string $group, ...$args)
    {
        return $this->fetchMultipleSchema($station, [$this], $group, ...$args);
    }

    protected function fetchMultipleSchema(Station $station, array $customizers, string $group, ...$args)
    {
        $schema =
            collect($customizers)
                ->map(fn($cus) => $cus->getAllSchemas()[$group] ?? [])
                ->flatten(1)
                ->sortBy([3, 2]) // Sort by y, x
                ->groupBy(3);

        $key = [];
        foreach ($schema as $rowSchema) {
            $row = [];
            foreach ($rowSchema as [$type, $builder, $x, $y, , $rtl]) {
                $schemaResult = $station->fireSignAs($this->sign, $builder, ...$args);
                switch ($type) {
                    // Single key
                    case 0:
                        $row[] = $schemaResult;
                        break;

                    // Row
                    case 1:
                        $key[] = $rtl ?? $this->isRtl() ? array_reverse($schemaResult) : $schemaResult;
                        break;

                    // Schema
                    case 2:
                        array_push($key, ...$rtl ?? $this->isRtl() ? KeyFormatter::rtl($schemaResult) : $schemaResult);
                        break;
                }
            }

            if ($row) {
                $key[] = $this->isRtl() ? array_reverse($row) : $row;
            }
        }

        return $key;
    }

    protected array $actions = [];

    public function insertAction(string $on, Closure $callback, bool $merge = true)
    {
        if ($merge)
            @$this->actions[$on][] = $callback;
        else
            $this->actions[$on] = [$callback];

        return $this;
    }

}