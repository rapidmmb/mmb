<?php

namespace Mmb\Support\Db;

/**
 * @deprecated
 */
trait HasFinder
{

    /**
     * Get current model
     *
     * @return ?static
     */
    public static function current()
    {
//        return ModelFinderDeprecated::current(static::class);
    }

    /**
     * Store to finder
     *
     * @return $this
     */
    public function finderStore()
    {
//        ModelFinderDeprecated::store($this);
//        return $this;
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
//        return ModelFinderDeprecated::find(static::class, $id, $or);
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
//        return ModelFinderDeprecated::findBy(static::class, $key, $id, $or);
    }

}
