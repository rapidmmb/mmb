<?php

namespace Mmb\Support\Db;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mmb\Context;
use Mmb\Exceptions\AbortException;

class ModelFinder implements ArrayAccess
{

    public function __construct(
        public Context $context,
    )
    {
    }

    protected array $caches   = [];
    protected array $currents = [];

    /**
     * Find from model
     *
     * @param string               $model
     * @param mixed                $id
     * @param mixed                $default
     * @param string|null          $by
     * @param bool                 $withTrashed
     * @param int|string|true|null $orFail
     * @param mixed|null           $failMessage
     * @param bool                 $useCache
     * @return Model|mixed
     */
    public function find(
        string               $model,
        mixed                $id,
        mixed                $default = null,
        ?string              $by = null,
        bool                 $withTrashed = false,
        null|int|string|true $orFail = null,
        mixed                $failMessage = null,
        bool                 $useCache = true,
    )
    {
        if ($by === '')
        {
            $by = null;
        }

        if ($useCache)
        {
            if (isset($by))
            {
                foreach ($this->caches[$model] ?? [] as $record)
                {
                    if ($record->getAttribute($by) == $id)
                    {
                        return $record;
                    }
                }
            }
            else
            {
                if (isset($this->caches[$model][$id]))
                {
                    return $this->caches[$model][$id];
                }
            }
        }

        /** @var Builder $query */
        $query = $model::query();

        $query = isset($by) ? $query->where($by, $id) : $query->whereKey($id);

        if ($withTrashed)
        {
            $query = $this->applyWithTrashed($model, $query);
        }

        $record = $query->first();

        if ($record === null)
        {
            return $this->runDefault($default, $orFail, $failMessage);
        }

        if (!$useCache)
        {
            return $record;
        }

        return @$this->caches[$model][$record->getKey()] = $record;
    }

    /**
     * Find by key and caching
     *
     * @param string               $model
     * @param string|null          $key
     * @param mixed                $value
     * @param mixed                $default
     * @param bool                 $withTrashed
     * @param int|string|true|null $orFail
     * @param mixed|null           $failMessage
     * @param bool                 $useCache
     * @return Model|mixed
     */
    public function findBy(
        string               $model,
        ?string              $key,
        mixed                $value,
        mixed                $default = null,
        bool                 $withTrashed = false,
        null|int|string|true $orFail = null,
        mixed                $failMessage = null,
        bool                 $useCache = true,
    )
    {
        return $this->find($model, $value, $default, $key, $withTrashed, $orFail, $failMessage, $useCache);
    }

    /**
     * Find model or fail
     *
     * @param string      $model
     * @param mixed       $id
     * @param string|null $by
     * @param bool        $withTrashed
     * @param int|string  $code
     * @param mixed|null  $message
     * @param bool        $useCache
     * @return Model|mixed
     */
    public function findOrFail(
        string     $model,
        mixed      $id,
        ?string    $by = null,
        bool       $withTrashed = false,
        int|string $code = 404,
        mixed      $message = null,
        bool       $useCache = true,
    )
    {
        return $this->find($model, $id, null, $by, $withTrashed, $code, $message, $useCache);
    }

    /**
     * Find model or fail
     *
     * @param string     $model
     * @param string     $key
     * @param mixed      $value
     * @param bool       $withTrashed
     * @param int|string $code
     * @param mixed|null $message
     * @param bool       $useCache
     * @return Model|mixed
     */
    public function findByOrFail(
        string     $model,
        string     $key,
        mixed      $value,
        bool       $withTrashed = false,
        int|string $code = 404,
        mixed      $message = null,
        bool       $useCache = true,
    )
    {
        return $this->findOrFail($model, $value, null, $key, $withTrashed, $code, $message, $useCache);
    }

    /**
     * Store model to caches
     *
     * @param Model|array $records
     * @return mixed
     */
    public function store(Model|array $records)
    {
        if (is_array($records))
        {
            $ids = [];
            foreach ($records as $record)
            {
                $ids[] = $this->store($record);
            }

            return $ids;
        }

        @$this->caches[$records::class][$key = $records->getKey()] = $records;
        return $key;
    }

    /**
     * Store a dynamic model to caches
     *
     * @param Model|array $records
     * @param string|null $by
     * @return mixed
     */
    public function storeDynamic(Model|array $records, ?string $by = null)
    {
        if (is_array($records))
        {
            $ids = [];
            foreach ($records as $record)
            {
                $ids[] = $this->storeDynamic($record, $by);
            }

            return $ids;
        }

        $this->store($records);
        return class_basename($records) . ':' . (isset($by) ? $records->getAttribute($by) : $records->getKey());
    }

    /**
     * Forget model or model class from caches
     *
     * @param string|Model $record
     * @return void
     */
    public function forget(string|Model $record)
    {
        if (is_string($record))
        {
            unset($this->caches[$record]);
        }
        else
        {
            unset($this->caches[$record::class][$record->getKey()]);
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
     * @param Model $record
     * @return Model
     */
    public function storeCurrent(Model $record)
    {
        $this->store($record);
        $this->currents[$record::class] = $record;

        return $record;
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
        return @$this->currents[$model];
    }

    /**
     * Checks a model current value is exists
     *
     * @param class-string $model
     * @return bool
     */
    public function hasCurrent(string $model): bool
    {
        return array_key_exists($model, $this->currents);
    }

    /**
     * Forget a model current value
     *
     * @param class-string $model
     * @return void
     */
    public function forgetCurrent(string $model): void
    {
        unset($this->currents[$model]);
    }

    /**
     * Find from multiple models by id
     *
     * @param array                $classes
     * @param mixed                $id
     * @param null                 $default
     * @param string|null          $by
     * @param bool                 $withTrashed
     * @param int|string|true|null $orFail
     * @param mixed|null           $failMessage
     * @param bool                 $useCache
     * @return Model|mixed
     */
    public function findDynamic(
        array                $classes,
        mixed                $id,
        mixed                $default = null,
        ?string              $by = null,
        bool                 $withTrashed = false,
        null|int|string|true $orFail = null,
        mixed                $failMessage = null,
        bool                 $useCache = true,
    )
    {
        if (is_string($id))
        {
            @[$classBase, $id] = explode(':', $id, 2);

            foreach ($classes as $class)
            {
                if (class_basename($class) == $classBase)
                {
                    return $this->find($class, $id, $default, $by, $withTrashed, $orFail, $failMessage, $useCache);
                }
            }
        }

        return $this->runDefault($default, $orFail, $failMessage);
    }

    /**
     * Find by key and caching
     *
     * @param array                $classes
     * @param string               $key
     * @param mixed                $value
     * @param null                 $default
     * @param bool                 $withTrashed
     * @param int|string|true|null $orFail
     * @param mixed|null           $failMessage
     * @param bool                 $useCache
     * @return Model|mixed
     */
    public function findDynamicBy(
        array                $classes,
        string               $key,
        mixed                $value,
        mixed                $default = null,
        bool                 $withTrashed = false,
        null|int|string|true $orFail = null,
        mixed                $failMessage = null,
        bool                 $useCache = true,
    )
    {
        @[$classBase, $id] = explode(':', $value, 2);

        foreach ($classes as $class)
        {
            if (class_basename($class) == $classBase)
            {
                return $this->findBy($class, $key, $id, $default, $withTrashed, $orFail, $failMessage, $useCache);
            }
        }

        return value($default);
    }

    /**
     * Find dynamic model or fail
     *
     * @param array           $classes
     * @param mixed           $id
     * @param string|null     $by
     * @param bool            $withTrashed
     * @param int|string|null $code
     * @param mixed|null      $message
     * @param bool            $useCache
     * @return Model|mixed
     */
    public function findDynamicOrFail(
        array      $classes,
        mixed      $id,
        ?string    $by = null,
        bool       $withTrashed = false,
        int|string $code = null,
        mixed      $message = null,
        bool       $useCache = true,
    )
    {
        return $this->findDynamic($classes, $id, null, $by, $withTrashed, $code, $message, $useCache);
    }

    /**
     * Find dynamic model by key or fail
     *
     * @param array           $classes
     * @param string          $key
     * @param mixed           $id
     * @param bool            $withTrashed
     * @param int|string|null $code
     * @param mixed|null      $message
     * @param bool            $useCache
     * @return Model|mixed
     */
    public function findDynamicByOrFail(
        array      $classes,
        string     $key,
        mixed      $id,
        bool       $withTrashed = false,
        int|string $code = null,
        mixed      $message = null,
        bool       $useCache = true,
    )
    {
        return $this->findDynamicBy($classes, $key, $id, null, $withTrashed, $code, $message, $useCache);
    }

    /**
     * Checks the model that has soft deletes
     *
     * @param string|Model $model
     * @return bool
     */
    protected function hasSoftDeletes(string|Model $model)
    {
        return $model::hasGlobalScope(SoftDeletingScope::class);
    }

    /**
     * Apply [withTrashed] scope if available
     *
     * @param string|Model $model
     * @param              $query
     * @return mixed
     */
    protected function applyWithTrashed(string|Model $model, $query)
    {
        return $this->hasSoftDeletes($model) ? $query->withTrashed() : $query;
    }

    /**
     * Run the default action
     *
     * @param $default
     * @param $error
     * @param $message
     * @return mixed
     */
    protected function runDefault($default, $error, $message)
    {
        if ($error)
        {
            throw new AbortException($error === true ? 404 : $error, $message);
        }

        return value($default);
    }


    public function offsetExists(mixed $offset): bool
    {
        return $this->hasCurrent($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->current($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_object($value) || get_class($value) != $offset) {
            throw new \TypeError("The model is not same with value type");
        }

        $this->storeCurrent($value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->forgetCurrent($offset);
    }
}