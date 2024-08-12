<?php

namespace Mmb\Support\Serialize;

class ShortDev
{

    /**
     * Add class prefix to an array
     *
     * @param array  $names
     * @param string $class
     * @return array
     */
    public static function addPrefix(array $names, string $class) : array
    {
        foreach ($names as $key => $value)
        {
            if (!str_contains($value, '.'))
            {
                $names[$key] = $class . '.' . $value;
            }
        }

        return $names;
    }

}
