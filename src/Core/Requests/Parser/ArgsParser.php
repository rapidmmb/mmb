<?php

namespace Mmb\Core\Requests\Parser;

use Illuminate\Support\Facades\Facade;
use Mmb\Core\Requests\RequestApi;

/**
 * @method static void on(string $name, $value)
 * @method static void onMethod(string $name, string|array $method, $value)
 * @method static void setDefault(string $name, $value)
 * @method static void setDefaultOn(string $name, string|array $method, $value)
 * @method static void merge(array ...$items)
 * @method static array normalize(RequestApi $request)
 */
class ArgsParser extends Facade
{

    protected static function getFacadeAccessor()
    {
        return ArgsParserFactory::class;
    }

}
