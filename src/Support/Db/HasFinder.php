<?php

namespace Mmb\Support\Db;

trait HasFinder
{

    /**
     * Get current model
     *
     * @return ?static
     */
    public static function current()
    {
        return ModelFinder::current(static::class);
    }

    /**
     * Store to finder
     *
     * @return $this
     */
    public function finderStore()
    {
        ModelFinder::store($this);
        return $this;
    }

    /**
     * Find with cache
     *
     * @param $id
     * @param $or
     * @return static|mixed
     */
    public static function findCache($id, $or = null)
    {
        return ModelFinder::find(static::class, $id, $or);
    }

    /**
     * Find with cache by key
     *
     * @param string $key
     * @param        $id
     * @param        $or
     * @return static|mixed
     */
    public static function findCacheBy(string $key, $id, $or = null)
    {
        return ModelFinder::findBy(static::class, $key, $id, $or);
    }

}
