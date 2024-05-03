<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Callbacks\Callback;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;

if(!function_exists('bot'))
{
    function bot() : ?Bot
    {
        return app(Bot::class);
    }
}

if(!function_exists('upd'))
{
    function upd() : ?Update
    {
        return app(Update::class);
    }
}

if(!function_exists('msg'))
{
    function msg() : ?Message
    {
        return upd()?->getMessage();
    }
}

if(!function_exists('callback'))
{
    function callback() : ?Callback
    {
        return upd()?->callbackQuery;
    }
}

if(!function_exists('smartTypeOf'))
{
    function smartTypeOf($value) : string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}

if(!function_exists('byLang'))
{
    function byLang(...$values)
    {
        return array_key_exists($key = Lang::getLocale(), $values) ?
            $values[$key] : (
                array_key_exists($key = Lang::getFallback(), $values) ?
                    $values[$key] :
                    null
            );
    }
}

if(!function_exists('___'))
{
    function ___(...$values)
    {
        return value(byLang(...$values));
    }
}

if(!function_exists('trim2'))
{
    function trim2(string|array $value)
    {
        if(is_string($value))
            $value = explode("\n", $value);

        return trim(implode("\n", array_map('trim', $value)));
    }
}

if(!function_exists('blade'))
{
    function blade(string $value, array $data = [])
    {
        return Blade::render($value, $data);
    }
}

if(!function_exists('bladeText'))
{
    function bladeText(string $value, array $data = [])
    {
        return trim2(Blade::render($value, $data));
    }
}
