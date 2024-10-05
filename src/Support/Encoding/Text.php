<?php

namespace Mmb\Support\Encoding;

use BackedEnum;

class Text
{

    protected static array $modes = [
        'none' => Modes\None::class,
        'html' => Modes\Html::class,
        'markdown' => Modes\Markdown::class,
        'markdown2' => Modes\Markdown2::class,
    ];

    public static function defineMode(string $mode, string|Modes\Mode $object)
    {
        static::$modes[strtolower($mode)] = $object;
    }

    public static function mode(string $mode) : Modes\Mode
    {
        $mode = strtolower($mode);
        $object = static::$modes[$mode] ?? null;

        if (is_null($object))
        {
            throw new Modes\ModeNotFoundException("Mode [$mode] not found");
        }

        if (is_string($object))
        {
            $object = static::$modes[$mode] = new $object;
        }

        return $object;
    }

    public static function html(string $text) : string
    {
        return static::mode('html')->text($text)->toString();
    }

    public static function markdown(string $text) : string
    {
        return static::mode('markdown')->text($text)->toString();
    }

    public static function markdown2(string $text) : string
    {
        return static::mode('markdown2')->text($text)->toString();
    }

    public static function userFriendly($value) : string
    {
        return match (true)
        {
            is_bool($value) => $value ? __('mmb::user-friendly.bool-true') : __('mmb::user-friendly.bool-false'),
            is_null($value) => __('mmb::user-friendly.null'),

            $value instanceof BackedEnum => method_exists($value, 'getLabel') ? $value->getLabel() : $value->value,

            default => (string) $value,
        };
    }

}
