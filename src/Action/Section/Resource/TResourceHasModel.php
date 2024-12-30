<?php

namespace Mmb\Action\Section\Resource;

use Illuminate\Database\Eloquent\Model;

trait TResourceHasModel
{

    protected function getModelClass()
    {
        return $this->model;
    }

    public function getRecordFrom($model)
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

        return $this->context->finder->find($modelClass, $model, withTrashed: true, orFail: true);
    }

    public function getIdFrom($record)
    {
        if ($record instanceof Model)
        {
            $this->context->finder->store($record);
            return $record->getKey();
        }

        return $record;
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
