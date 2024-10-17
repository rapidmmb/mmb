<?php

namespace Mmb\Support\Db;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Model|mixed find(string $model, mixed $id, mixed $default = null, ?string $by = null, bool $withTrashed = false, null|int|string|true $orFail = null, mixed $failMessage = null, bool $useCache = true)
 * @method static Model|mixed findBy(string $model, ?string $key, mixed $value, mixed $default = null, bool $withTrashed = false, null|int|string|true $orFail = null, mixed $failMessage = null, bool $useCache = true)
 * @method static Model|mixed findOrFail(string $model, mixed $id, ?string $by = null, bool $withTrashed = false, int|string $code = 404, mixed $message = null, bool $useCache = true)
 * @method static Model|mixed findByOrFail(string $model, string $key, mixed $value, bool $withTrashed = false, int|string $code = 404, mixed $message = null, bool $useCache = true)
 * @method static Model|mixed findDynamic(array $classes, mixed $id, mixed $default = null, ?string $by = null, bool $withTrashed = false, null|int|string|true $orFail = null, mixed $failMessage = null, bool $useCache = true)
 * @method static Model|mixed findDynamicBy(array $classes, string $key, mixed $value, mixed $default = null, bool $withTrashed = false, null|int|string|true $orFail = null, mixed $failMessage = null, bool $useCache = true)
 * @method static Model|mixed findDynamicOrFail(array $classes, mixed $id, ?string $by = null, bool $withTrashed = false, int|string $code = null, mixed $message = null, bool $useCache = true)
 * @method static Model|mixed findDynamicByOrFail(array $classes, string $key, mixed $id, bool $withTrashed = false, int|string $code = null, mixed $message = null, bool $useCache = true)
 * @method static mixed       store(Model|array $records)
 * @method static mixed       storeDynamic(Model|array $records, ?string $by = null)
 * @method static void        forget(string|Model $record)
 * @method static void        clear()
 * @method static Model       storeCurrent(Model $record)
 * @method static Model       current(string $model)
 */
class ModelFinder extends Facade
{

    protected static function getFacadeAccessor()
    {
        return FinderFactory::class;
    }

}
