<?php

namespace Mmb\Support\Db;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InheritableScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        $subQuery = $model->newModelQuery();

        $ref = new \ReflectionClass($model);
        $params = [];
        $base = $model->getFillable();
        $children = $model;
        while(($ref = $ref->getParentClass()) && $ref->name != InheritableModel::class)
        {
            $instance = $ref->newInstance();
            $subQuery->join($instance->getTable(), "{$instance->getTable()}.{$instance->getKeyName()}", '=', "{$children->getTable()}.base_id");

            foreach($instance->getFillable() as $column)
            {
                if(!in_array($column, $base) && !array_key_exists($column, $params))
                {
                    $params[$column] = $instance->getTable();
                }
            }

            $children = $instance;
        }

        $selects = [$model->getTable() . '.*'];
        foreach($params as $column => $table)
        {
            $selects[] = "{$table}.{$column} as {$column}";
        }

        $subQuery->select(...$selects);

        $builder->from($subQuery, $model->getTable());
    }

}
