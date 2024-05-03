<?php

namespace Mmb\Action\Section\Resource;

use Illuminate\Database\Eloquent\Model;
use Mmb\Support\Db\ModelFinder;

trait TResourceHasModel
{

    protected function getModelClass()
    {
        return $this->model;
    }

    public function getModelFrom($model)
    {
        $modelClass = $this->getModelClass();

        if($model instanceof Model)
        {
            if($model instanceof $modelClass)
            {
                return $model;
            }

            throw new \TypeError(sprintf("Expected model of type [%s], given [%s]", $modelClass, smartTypeOf($model)));
        }

        return ModelFinder::findOrFail($modelClass, $model);
    }

    public function getIdFrom($model)
    {
        if($model instanceof Model)
        {
            ModelFinder::store($model);
            return $model->getKey();
        }

        return $model;
    }

    protected string $as;

    public function as(string $name)
    {
        $this->as = $name;
    }

    /**
     * Set the model globally and fire action
     *
     * @param            $action
     * @param array      $args
     * @param array      $dynamicArgs
     * @param array|null $openArgs
     * @return void
     */
    public function fireInner($action, array $args = [], array $dynamicArgs = [], array $openArgs = null)
    {
        if(!isset($this->as))
        {
            throw new \InvalidArgumentException("Model name is not set, use \"->as(NAME)\" for the module");
        }

        $this->set($this->as, $this->getDynArg('model'));
        $this->fireAction($action, $args, $dynamicArgs, $openArgs);
    }

}
