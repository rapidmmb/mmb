<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Callbacks\CallbackQuery;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Support\Pov\POV;
use Mmb\Support\Pov\POVBuilder;

if (!function_exists('bot')) {
    /**
     * Get main bot instance
     *
     * If you have a multi bot handler application, use `$context->bot` instead.
     *
     * @return Bot|null
     */
    function bot(): ?Bot
    {
        return app(Bot::class);
    }
}

if (!function_exists('update')) {
    /**
     * @return Update|null
     * @deprecated
     */
    function update(): ?Update
    {
//        return app(Update::class);
        throw new \BadMethodCallException();
    }
}

if (!function_exists('msg')) {
    /**
     * @deprecated
     */
    function msg(): ?Message
    {
//        return update()?->getMessage();
        throw new \BadMethodCallException();
    }
}

if (!function_exists('callback')) {
    /**
     * @deprecated
     */
    function callback(): ?CallbackQuery
    {
//        return update()?->callbackQuery;
        throw new \BadMethodCallException();
    }
}

if (!function_exists('smartTypeOf')) {
    function smartTypeOf($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}

if (!function_exists('byLang')) {
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

if (!function_exists('___')) {
    function ___(...$values)
    {
        return value(byLang(...$values));
    }
}

if (!function_exists('trim2')) {
    /**
     * @deprecated 
     *
     * @param string|array $value
     * @return string
     */
    function trim2(string|array $value)
    {
        if (is_string($value))
            $value = explode("\n", $value);

        return trim(implode("\n", array_map('trim', $value)));
    }
}

if (!function_exists('pov')) {
    function pov()
    {
        return POV::make();
    }
}
