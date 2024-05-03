<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Mmb\Support\Caller\ParameterPassingInstead;
use Mmb\Support\Db\ModelFinder;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FindDynamicBy extends ParameterPassingInstead
{

    public function __construct(
        public string $key,
        public ?int $error = 404,
    )
    {
    }

    public function getInsteadOf($value)
    {
        if($value instanceof Model)
        {
            ModelFinder::store($value);

            return class_basename($value) . ':' . ($this->key === '' ? $value->getKey() : $value->{$this->key});
        }

        return $value;
    }

    public function cast($value, string $class)
    {
        return $this->castMultiple($value, [$class]);
    }

    public function castMultiple($value, array $classes)
    {
        if(is_object($value) || $value === null)
        {
            return $value;
        }

        if($this->error)
        {
            return ModelFinder::findDynamicBy($classes, $this->key, $value, fn() => abort($this->error));
        }
        else
        {
            return ModelFinder::findDynamicBy($classes, $this->key, $value);
        }
    }

}
