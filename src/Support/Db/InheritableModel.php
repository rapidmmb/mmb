<?php

namespace Mmb\Support\Db;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InheritableModel extends Model
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if($this->hasParent())
        {
            $this->with[] = 'base';
        }
    }

    /**
     * Boot class.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new InheritableScope());
        static::deleting(function($model)
        {
            if($model->hasParent())
            {
                $model->base->delete();
            }
        });
    }

    /**
     * @param $attributes
     * @param $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        if(!$exists && $attributes && ($parent = get_parent_class($this)) != InheritableModel::class)
        {
            $instance = parent::newInstance($attributes, $exists);
            $instance->setRelation('base', (new $parent)->newInstance($attributes, $exists));
            return $instance;
        }

        return parent::newInstance($attributes, $exists);
    }

    protected $isFilling = false;

    /**
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        $this->isFilling = true;
        parent::fill($attributes);
        $this->isFilling = false;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if(
            !$this->isRelation($key) &&
            $key != $this->getKeyName() &&
            !array_key_exists($key, $this->attributes) &&
            $this->hasParent() &&
            array_key_exists($key, $this->base->attributes)
        )
        {
            return $this->base->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     * @return $this|mixed
     */
    public function setAttribute($key, $value)
    {
        if(
            !$this->isFilling &&
            !$this->isRelation($key) &&
            $key != $this->getKeyName() &&
            !array_key_exists($key, $this->attributes) &&
            $this->hasParent() &&
            $key != $this->getCreatedAtColumn() &&
            $key != $this->getUpdatedAtColumn() &&
            array_key_exists($key, $this->base->attributes)
        )
        {
            $this->base->setAttribute($key, $value);
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if(!$this->exists && $this->hasParent())
        {
            $this->base->save();
            $this->base_id = $this->base->getKey();

            if(parent::save($options + ['ignore_root' => true]))
            {
                if(!isset($options['ignore_root']))
                {
                    $root = $this->getRootBase();
                    $root->object_id = $this->getKey();
                    $root->object_type = static::class;
                    $root->save();
                }
                return true;
            }
            else
            {
                $this->base->delete();
                return false;
            }
        }

        if(parent::save($options))
        {
            return !$this->hasParent() || $this->base->save();
        }

        return false;
    }

    /**
     * Check the class has parent.
     *
     * @return bool
     */
    public static function hasParent()
    {
        return get_parent_class(static::class) != InheritableModel::class;
    }

    /**
     * Parent relationship.
     *
     * @return BelongsTo
     */
    public function base()
    {
        return $this->belongsTo(get_parent_class($this), 'base_id');
    }

    /**
     * Real object relationship.
     *
     * @return MorphTo
     */
    public function object()
    {
        if($this->hasParent())
        {
            throw new \InvalidArgumentException("object() relationship is just for root class.");
        }

        return $this->morphTo(null, 'object_type', 'object_id');
    }

    /**
     * Get the root model.
     *
     * @return static
     */
    public function getRootBase()
    {
        $ptr = $this;
        while($ptr->hasParent())
        {
            $ptr = $ptr->base;
        }

        return $ptr;
    }

    /**
     * Get the real object.
     *
     * @return static
     */
    public function getObject()
    {
        if($this->hasParent())
        {
            return $this->getRootBase()->getObject();
        }

        if(!isset($this->attributes['object_id']))
        {
            return $this;
        }

        return $this->object ?? $this;
    }

    /**
     * Get model as parent class.
     *
     * @template T
     * @param class-string<T> $class
     * @return T|null
     */
    public function as(string $class)
    {
        if(static::class == $class)
        {
            return $this;
        }

        $ptr = $this;
        while($ptr->hasParent())
        {
            $ptr = $ptr->base;

            if(get_class($ptr) == $class)
            {
                return $ptr;
            }
        }

        return null;
    }

    /**
     * Get parent id.
     *
     * @param string $class
     * @return mixed
     */
    public function idAs(string $class)
    {
        return $this->as($class)?->getKey();
    }

    /**
     * Get parent attribute.
     *
     * @param string $class
     * @param string $key
     * @return mixed
     */
    public function getAs(string $class, string $key)
    {
        return $this->as($class)?->getAttribute($key);
    }


    public static function scopeFirstObject(Builder $builder)
    {
        if(static::hasParent())
        {
            return $builder->first()->getObject();
        }

        return $builder->first()->object;
    }

    public static function scopeGetObjects(Builder $builder)
    {
        if(static::hasParent())
        {
            $ids = $builder->pluck('base_id');
            return get_parent_class(static::class)::query()->whereIn('base_id', $ids)->getObjects();
        }

        return $builder
            ->without('base')
            ->with('object')
            ->get()
            ->map(fn($item) => $item->getObject());
    }

}
