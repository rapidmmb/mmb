<?php

namespace Mmb\Action\Section;

use Mmb\Action\Update\UpdateHandling;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\ModelFinder;

class GlobalDialogHandler implements UpdateHandling
{

    public function __construct(
        public string|array|null $models = null,
    )
    {
    }

    public static function make(
        string|array|null $models = null,
    )
    {
        return new static($models);
    }

    public $found;

    public Update $lastUpdate;

    public function check(Update $update)
    {
        $this->lastUpdate = $update;

        if (
            ($data = $update->callbackQuery?->data) &&
            str_starts_with($data, '#dialog:')
        )
        {
            @[$target, $id, $action] = explode(':', substr($data, 8), 3);

            if (!class_exists($target) || !$id)
            {
                return false;
            }

            if (isset($this->models) && (is_string($this->models) ? $target != $this->models : !in_array($target, $this->models)))
            {
                return false;
            }

            if ($found = ModelFinder::find($target, $id))
            {
                $this->found = $found;

                if (!$this->validate())
                {
                    return false;
                }

                return true;
            }
        }

        elseif (
            $data &&
            str_starts_with($data, '#df:')
        )
        {
            @[$class, $method, $attrs, $action] = explode(':', substr($data, 4), 3);
            dump($attrs);

            if (!class_exists($class) || !method_exists($class, $method))
            {
                return false;
            }

            $this->found = [$class, $method, $action];
            return true;
        }

        return false;
    }

    public function validate()
    {
        return !is_object($this->found) || $this->found->user_id == $this->lastUpdate->bot()->guard()->user()->id;
    }

    public function handleUpdate(Context $context, Update $update)
    {
        if ($this->check($update))
        {
            if (is_object($this->found))
            {
                $this->found->target?->handle($context, $update);
            }
            else
            {
                [$class, $method, $action] = $this->found;

                $class::makeByContext($context)->dialog($method)->handle($update);
            }
        }

        $update->skipHandler();
    }

    public static function makeQuery(string $model, $id, $action)
    {
        return "#dialog:$model:$id:$action";
    }

    public static function makeFixedQuery(string $class, string $method, $action, array $within)
    {
        $with = json_encode($within);

        dump("#df:$class:$method:$with:$action");
        return "#df:$class:$method:$with:$action";
    }

}
