<?php

use Illuminate\Support\Facades\Lang;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Callbacks\CallbackQuery;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Exceptions\AbortException;
use Mmb\Support\Pov\POV;

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
                @$values['default']
            );
    }
}

if (!function_exists('___')) {
    /**
     * @deprecated
     * @param ...$values
     * @return mixed
     */
    function ___(...$values)
    {
        return value(byLang(...$values));
    }
}

if (!function_exists('trim2')) {
    /**
     * @param string|array $value
     * @return string
     *
     * @deprecated
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

if (!function_exists('denied')) {
    /**
     * Abort the code
     *
     * @param int|string $errorType
     * @param mixed|null $errorMessage
     * @param Throwable|null $previous
     * @return mixed
     */
    function denied(int|string $errorType, mixed $errorMessage = null, ?\Throwable $previous = null)
    {
        throw new AbortException($errorType, $errorMessage, $previous);
    }
}

if (!function_exists('denied_if')) {
    /**
     * Abort the code when condition is true
     *
     * @param $condition
     * @param int|string $errorType
     * @param mixed|null $errorMessage
     * @param Throwable|null $previous
     * @return void
     */
    function denied_if($condition, int|string $errorType, mixed $errorMessage = null, ?\Throwable $previous = null)
    {
        if (!value($condition)) {
            return;
        }

        throw new AbortException($errorType, $errorMessage, $previous);
    }
}

if (!function_exists('denied_unless')) {
    /**
     * Abort the code when condition is not true
     *
     * @param $condition
     * @param int|string $errorType
     * @param mixed|null $errorMessage
     * @param Throwable|null $previous
     * @return void
     */
    function denied_unless($condition, int|string $errorType, mixed $errorMessage = null, ?\Throwable $previous = null)
    {
        if (value($condition)) {
            return;
        }

        throw new AbortException($errorType, $errorMessage, $previous);
    }
}
