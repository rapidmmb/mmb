<?php

namespace Mmb\Support\KeySchema;

final class StaticIfScopes
{

    protected static array $ifScopes = [];

    public static function isNotSetIfScope(string $name = '_')
    {
        return !isset(self::$ifScopes[$name]);
    }

    public static function setIfScope(string $name = '_')
    {
        self::$ifScopes[$name] = true;
    }

    public static function removeIfScope(string $name = '_')
    {
        unset(self::$ifScopes[$name]);
    }

}