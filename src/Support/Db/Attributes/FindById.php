<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;
use Mmb\Support\Db\ModelFinder;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindById extends FindBy
{

    public function __construct(
        ?int $error = 404,
    )
    {
        parent::__construct('', $error);
    }

    public function cast($value, string $class)
    {
        if($value instanceof $class || $value === null)
        {
            return $value;
        }

        if($this->error)
        {
            return ModelFinder::find($class, $value, fn() => abort($this->error));
        }
        else
        {
            return ModelFinder::find($class, $value);
        }
    }

}
