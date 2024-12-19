<?php

namespace Mmb\KeySchema;

class KeyboardSchema
{

    public function __construct(
        public KeyboardInterface $base,
        public                   $key,
        public string            $name = 'main',
        public bool              $fixed = false,
        public bool              $exclude = false,
    )
    {
    }

    public function normalizeKey(bool $storable = false): array
    {
        $map = value($this->key);
        $rawKey = [];
        $keyDataMap = [];
        $storableKeyMap = [];

        if (is_iterable($map)) {
            $map = iterator_to_array($map);
        }

        if ($map === null) {
            return [];
        }

        if (!is_array($map)) {
            throw new \TypeError("Keyboard should be array, given " . gettype($map));
        }

        foreach ($map as $rowKey => $row) {
            $row = value($row);

            if (is_iterable($row)) {
                $row = iterator_to_array($row);
            }

            if (!$row) {
                continue;
            }

            if (!is_array($row)) {
                throw new \TypeError("Keyboard row should be array at [$rowKey], given " . gettype($map));
            }

            $keyboardRow = [];
            foreach ($row as $columnKey => $column) {
                $column = value($column);

                if (is_iterable($column)) {
                    $column = iterator_to_array($column);
                }

                if (!$column) {
                    continue;
                }

                if ($column instanceof KeyInterface) {
                    if (!$column->isDisplayed()) {
                        continue;
                    }

                    $rawAttributes = $column->toArray();
                    $key = $column;
                } elseif (is_array($column)) {
                    $rawAttributes = $column;
                    $key = null;
                } else {
                    throw new \TypeError(
                        "Keyboard column should be array or MenuKey at [$rowKey][$columnKey], given " . gettype($column),
                    );
                }

                if ($key && $key->isIncluded() && null !== $data = $key->getUniqueData($this->base)) {
                    if ($storable && !$key->isStorable()) {
                        throw new \TypeError("Keyboard action with Closure value is not available for storable keyboard");
                    }

                    if ($storable) {
                        $storableKeyMap[$data] = $key->toStorable();
                    }

                    $keyDataMap[$data] = $key;
                }

                if (!$key || $key->isVisible()) {
                    $keyboardRow[] = $rawAttributes;
                }
            }

            if ($keyboardRow) {
                $rawKey[] = $keyboardRow;
            }
        }

        return [$rawKey, $keyDataMap, $storableKeyMap];
    }

}