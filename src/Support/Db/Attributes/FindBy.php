<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Mmb\Support\Caller\ParameterPassingInstead;
use Mmb\Support\Db\ModelFinder;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FindBy extends ParameterPassingInstead
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

            return $this->key === '' ? $value->getKey() : $value->{$this->key};
        }

        return $value;
    }

    public function cast($value, string $class)
    {
        if($value instanceof $class || $value === null)
        {
            return $value;
        }

        if($this->error)
        {
            return ModelFinder::findBy($class, $this->key, $value, fn() => abort($this->error));
        }
        else
        {
            return ModelFinder::findBy($class, $this->key, $value);
        }
    }

}
