<?php

namespace Mmb\Support\Db;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Model|mixed find(string $model, $id, $default = null)
 * @method static Model|mixed findBy(string $model, string $key, $id, $default = null)
 * @method static Model       findOrFail(string $model, $id)
 * @method static Model       findByOrFail(string $model, string $key, $id)
 * @method static Model|mixed findDynamic(array $classes, $id, $default = null)
 * @method static Model|mixed findDynamicBy(array $classes, string $key, $id, $default = null)
 * @method static Model       findDynamicOrFail(array $classes, $id)
 * @method static Model       findDynamicByOrFail(array $classes, string $key, $id)
 * @method static mixed       store(Model|array $model)
 * @method static mixed       storeDynamic(Model|array $model)
 * @method static void        forget(string|Model $model)
 * @method static void        clear()
 * @method static Model       storeCurrent(Model $model)
 * @method static Model       current(string $model)
 */
class ModelFinder extends Facade
{

    protected static function getFacadeAccessor()
    {
        return FinderFactory::class;
    }

}
