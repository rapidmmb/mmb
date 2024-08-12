<?php

namespace Mmb\Support\Db;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FinderFactory
{

    protected array $caches = [];
    protected array $currents = [];

    /**
     * Find from model by id
     *
     * @param string $model
     * @param        $id
     * @param        $default
     * @return Model|mixed
     */
    public function find(string $model, $id, $default = null)
    {
        if(isset($this->caches[$model][$id]))
        {
            return $this->caches[$model][$id];
        }

        $object = $model::find($id);

        if($object === null)
        {
            return value($default);
        }

        return @$this->caches[$model][$id] = $object;
    }

    /**
     * Find by key and caching
     *
     * @param string $model
     * @param string $key
     * @param        $value
     * @param        $default
     * @return Model|mixed
     */
    public function findBy(string $model, string $key, $value, $default = null)
    {
        if($key === '')
        {
            return $this->find($model, $value, $default);
        }

        if(isset($this->caches[$model]))
        {
            /** @var Model $object */
            foreach($this->caches[$model] as $object)
            {
                if($object->getAttribute($key) == $value)
                {
                    return $object;
                }
            }
        }

        $object = $model::where($key, $value)->first();

        if($object === null)
        {
            return value($default);
        }

        return @$this->caches[$model][$object->getKey()] = $object;
    }

    /**
     * Find model or fail
     *
     * @param string $model
     * @param        $id
     * @param int    $code
     * @return Model|mixed
     */
    public function findOrFail(string $model, $id, int $code = 404)
    {
        return $this->find($model, $id, fn() => abort($code));
    }

    /**
     * Find model by key or fail
     *
     * @param string $model
     * @param string $key
     * @param        $id
     * @param int    $code
     * @return Model|mixed
     */
    public function findByOrFail(string $model, string $key, $id, int $code = 404)
    {
        return $this->findBy($model, $key, $id, fn() => abort($code));
    }

    /**
     * Store model to caches
     *
     * @param Model|array $model
     * @return mixed
     */
    public function store(Model|array $model)
    {
        if(is_array($model))
        {
            $ids = [];
            foreach($model as $model2)
            {
                $ids[] = $this->store($model2);
            }

            return $ids;
        }

        @$this->caches[$model::class][$key = $model->getKey()] = $model;
        return $key;
    }

    /**
     * Store a dynamic model to caches
     *
     * @param Model|array $model
     * @return mixed
     */
    public function storeDynamic(Model|array $model)
    {
        if(is_array($model))
        {
            $ids = [];
            foreach($model as $model2)
            {
                $ids[] = $this->storeDynamic($model2);
            }

            return $ids;
        }

        $this->store($model);
        return class_basename($model) . ':' . $model->getKey();
    }

    /**
     * Forget model or model class from caches
     *
     * @param string|Model $model
     * @return void
     */
    public function forget(string|Model $model)
    {
        if(is_string($model))
        {
            unset($this->caches[$model]);
        }
        else
        {
            unset($this->caches[$model::class][$model->getKey()]);
        }
    }

    /**
     * Clear caches
     *
     * @return void
     */
    public function clear()
    {
        $this->caches = [];
    }

    /**
     * Store current of model to cache
     *
     * @param Model $model
     * @return Model
     */
    public function storeCurrent(Model $model)
    {
        $this->store($model);
        $this->currents[$model::class] = $model;

        // if($model instanceof Authenticatable)
        // {
            // auth()->setUser($model); // TODO: Remove
        // }

        return $model;
    }

    /**
     * Get current model
     *
     * @template T
     * @param class-string<T> $model
     * @return ?T
     */
    public function current(string $model)
    {
        // if (!isset($this->currents[$model]) && is_a($model, Authenticatable::class))
        // {
        //     Auth::guard('bot')->user(); // TODO : Remove
        // }

        return @$this->currents[$model];
    }

    /**
     * Find from multiple models by id
     *
     * @param array $classes
     * @param            $id
     * @param null       $default
     * @return Model|mixed
     */
    public function findDynamic(array $classes, $id, $default = null)
    {
        @[$classBase, $id] = explode(':', $id, 2);

        foreach($classes as $class)
        {
            if(class_basename($class) == $classBase)
            {
                return $this->find($class, $id, $default);
            }
        }

        return value($default);
    }

    /**
     * Find by key and caching
     *
     * @param array  $classes
     * @param string $key
     * @param        $value
     * @param null   $default
     * @return Model|mixed
     */
    public function findDynamicBy(array $classes, string $key, $value, $default = null)
    {
        @[$classBase, $id] = explode(':', $value, 2);

        foreach($classes as $class)
        {
            if(class_basename($class) == $classBase)
            {
                return $this->findBy($class, $key, $id, $default);
            }
        }

        return value($default);
    }

    /**
     * Find dynamic model or fail
     *
     * @param array  $classes
     * @param        $id
     * @param int    $code
     * @return Model|mixed
     */
    public function findDynamicOrFail(array $classes, $id, int $code = 404)
    {
        return $this->findDynamic($classes, $id, fn() => abort($code));
    }

    /**
     * Find dynamic model by key or fail
     *
     * @param array  $classes
     * @param string $key
     * @param        $id
     * @param int    $code
     * @return Model|mixed
     */
    public function findDynamicByOrFail(array $classes, string $key, $id, int $code = 404)
    {
        return $this->findDynamicBy($classes, $key, $id, fn() => abort($code));
    }

}
