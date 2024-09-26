<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Support\Facades\Facade;
use Mmb\Core\Updates\Update;

/**
 * @method void add(string $class)
 * @method void merge(array $classes)
 * @method void extend(string $class, Closure $callback)
 * @method void handle(Update $update, array $mergedHandlers = [])
 */
class Handle extends Facade
{

    protected static function getFacadeAccessor()
    {
        return HandleFactory::class;
    }

}