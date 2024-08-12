<?php

namespace Mmb\Support\Format;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Action\Form\FormKey;
use Mmb\Action\Section\MenuKey;

class KeyFormatter
{

    /**
     * Get key value. Call function if closure passed
     *
     * @param array|Closure $key
     * @return array|Closure
     */
    public static function value(array|Closure $key)
    {
        if ($key instanceof Closure)
        {
            $key = value($key);

            if ($key === null) return [];

            return iterator_to_array($key, false);
        }

        return $key;
    }

    /**
     * Get only e
     *
     * @param array $row
     * @return array[]
     */
    protected static function separateEnabled(array $row)
    {
        $enabled = [];
        $disabled = [];

        foreach ($row as $item)
        {
            if (
                ($item instanceof FormKey && !$item->enabled) ||
                ($item instanceof MenuKey && (!$item->isDisplayed() || !$item->isVisible()))
            )
            {
                $disabled[] = $item;
            }
            else
            {
                $enabled[] = $item;
            }
        }

        return [$enabled, $disabled];
    }

    public static function for(array|Closure $key)
    {
        return new KeyFormatterBuilder(
            static::value($key),
        );
    }


    /**
     * Convert key direction to rtl (actually reverse it)
     *
     * @param array|Closure $key
     * @return array
     */
    public static function rtl(array|Closure $key)
    {
        $key = static::value($key);

        foreach ($key as $y => $row)
        {
            if (is_array($row))
            {
                $key[$y] = array_reverse($row);
            }
        }

        return $key;
    }

    /**
     * Limit key rows to having a maximum columns.
     * Each rows that has more than $max columns, will wrap to next line (or removed)
     *
     * @param array|Closure $key
     * @param int           $max
     * @param bool          $wrap
     * @return array
     */
    public static function maxColumns(array|Closure $key, int $max, bool $wrap = true)
    {
        $key = static::value($key);

        $result = [];
        foreach ($key as $row)
        {
            if (is_array($row))
            {
                [$enabled, $disabled] = static::separateEnabled($row);
                if (count($enabled) > $max)
                {
                    if ($wrap)
                    {
                        $newRows = array_chunk($enabled, $max);
                        array_push($newRows[0], ...$disabled);
                        array_push($result, ...$newRows);
                    }
                    else
                    {
                        array_push($result, ...array_slice($enabled, 0, $max), ...$disabled);
                    }

                    continue;
                }
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Limit key rows to having a maximum columns.
     * Each rows that has more than $max columns, will wrap to next line.
     *
     * @param array|Closure $key
     * @param int           $max
     * @return array
     */
    public static function wrap(array|Closure $key, int $max)
    {
        return static::maxColumns($key, $max, true);
    }

    /**
     * Limit key rows to having a maximum columns.
     * Each rows that has more than $max columns, will remove last items.
     *
     * @param array|Closure $key
     * @param int           $max
     * @return array
     */
    public static function wrapHidden(array|Closure $key, int $max)
    {
        return static::maxColumns($key, $max, false);
    }

    /**
     * Resize a keyboard.
     * This function resize the keyboard to custom columns.
     *
     * @param array|Closure $key
     * @param int           $columns
     * @return array|Closure
     */
    public static function resize(array|Closure $key, int $columns)
    {
        $key = static::value($key);

        $keyFlatten = Arr::whereNotNull(Arr::flatten($key, 1));
        [$enabled, $disabled] = static::separateEnabled($keyFlatten);

        if ($enabled)
        {
            $key = array_chunk($enabled, $columns);
            array_push($key[0], ...$disabled);
        }

        return $key;
    }

    /**
     * Automatically resize a keyboard.
     * This function resize the keyboard, using their text length.
     * Size parameter is the maximum length for a full column span.
     *
     * @param array|Closure $key
     * @param int           $size
     * @return array|Closure
     */
    public static function resizeAuto(array|Closure $key, int $size = 40)
    {
        $key = static::value($key);

        $keyFlatten = Arr::whereNotNull(Arr::flatten($key, 1));
        [$enabled, $disabled] = static::separateEnabled($keyFlatten);

        if ($enabled)
        {
            $key = [];
            $lastRow = [];
            $lastMinCols = 20;
            foreach ($enabled as $item)
            {
                $length = mb_strlen($item instanceof MenuKey ? $item->getText() :
                    ($item instanceof FormKey ? $item->text :
                        (is_array($item) ? @$item['text'] : '')
                    ));

                $minColsCur = floor($size / $length);
                $minCols = min($minColsCur, $lastMinCols);

                if ($lastRow && count($lastRow) >= $minCols)
                {
                    $key[] = $lastRow;
                    $lastRow = [$item];
                    $lastMinCols = $minColsCur;
                }
                else
                {
                    $lastRow[] = $item;
                    $lastMinCols = $minCols;
                }
            }

            $key[] = $lastRow;
            array_push($key[0], ...$disabled);
        }

        return $key;
    }

}
