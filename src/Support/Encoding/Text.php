<?php

namespace Mmb\Support\Encoding;

use BackedEnum;

class Text
{

    public static function html(string $string) : string
    {
        return str_replace([
            '&', '<', '>',
        ], [
            "&amp;", "&lt;", "&gt;",
        ], $string);
    }

    public static function markdown(string $string) : string
    {
        return str_replace([
            "\\", '_', '*', '`', '['
        ], [
            "\\\\", "\\_", "\\*", "\\`", "\\[",
        ], $string);
    }

    public static function markdown2(string $string) : string
    {
        return preg_replace('/[\\\\_\*\[\]\(\)~`>\#\+\-=\|\{\}\.\!]/', '\\\\$0', $string);
    }

    public static function userFriendly($value) : string
    {
        return match (true)
        {
            is_bool($value) => $value ? __('mmb.user-friendly.bool-true') : __('mmb.user-friendly.bool-false'),
            is_null($value) => __('mmb.user-friendly.null'),

            $value instanceof BackedEnum => method_exists($value, 'getLabel') ? $value->getLabel() : $value->value,

            default => (string) $value,
        };
    }

}
