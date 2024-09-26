<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Support\Facades\Facade;
use Mmb\Core\Updates\Update;

/**
 * @method static void add(string $class)
 * @method static void merge(array $classes)
 * @method static void extend(string $class, Closure $callback)
 * @method static void handle(Update $update, array $mergedHandlers = [])
 */
class Handle extends Facade
{

    protected static function getFacadeAccessor()
    {
        return HandleFactory::class;
    }

}