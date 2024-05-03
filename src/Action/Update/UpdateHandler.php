<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Action;
use Mmb\Action\Memory\Step;
use Mmb\Action\Memory\StepHandlerPipe;
use Mmb\Action\Middle\MiddleAction;
use Mmb\Action\Middle\MiddleActionHandledUpdateHandling;
use Mmb\Action\Section\Controllers\CallbackControlHandler;
use Mmb\Action\Section\Controllers\InlineControlHandler;
use Mmb\Support\Db\ModelFinder;
use Mmb\Support\Step\Stepping;

class UpdateHandler extends Action
{

    /**
     * Check matching handler
     *
     * @return false
     */
    public function match()
    {
        return false;
    }

    protected ?string $model = null;

    protected ?string $modelBy = null;

    protected ?string $modelKey = '';

    protected ?array $modelCreateAttributes = null;

    /**
     * Get model class name
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get model id from
     */
    public function getModelBy()
    {
        return $this->modelBy;
    }

    /**
     * Get key to find model
     */
    public function getModelKey()
    {
        return $this->modelKey;
    }

    /**
     * Get attributes to create if the model doesn't exist
     */
    public function getModelCreateAttributes()
    {
        return $this->modelCreateAttributes;
    }

    /**
     * Get the stepping model
     *
     * @return Stepping|null
     */
    public function getStepping()
    {
        if($model = $this->getCurrentModel())
        {
            if($model instanceof Stepping)
            {
                return $model;
            }
        }

        return null;
    }

    /**
     * Find model
     *
     * @return Model|false|null
     */
    public final function findModel()
    {
        if($model = $this->getModel())
        {
            if($by = $this->get($this->getModelBy()))
            {
                $key = $this->getModelKey();
                $result = ModelFinder::findBy($model, $key, $by, false);

                if(!$result && $attrs = $this->getModelCreateAttributes())
                {
                    $result = $model::create($attrs);
                }

                if($result)
                {
                    ModelFinder::storeCurrent($result);
                }

                return $result;
            }
            else
            {
                return false;
            }
        }

        return null;
    }

    private $currentModel;

    /**
     * Get current model
     *
     * @return Model|false|null
     */
    public final function getCurrentModel()
    {
        return $this->currentModel ??= $this->findModel();
    }


    /**
     * Get value from update
     *
     * @param string $name
     * @return mixed
     */
    public final function get(string $name)
    {
        $ors = array_map('trim', explode('|', $name));

        $last = null;
        foreach($ors as $or)
        {
            $dots = explode('.', $or);
            $object = match($dots[0])
            {
                '@chat' => $this->update->getChat(),
                '@user' => $this->update->getUser(),
                default => $this->update,
            };
            if($object != $this->update)
                unset($dots[0]);

            foreach($dots as $dot)
            {
                if(!is_object($object) && !is_array($object))
                {
                    continue 2;
                }

                $object = @$object[$dot];
            }

            if($last = $object)
            {
                return $object;
            }
        }

        return $last;
    }


    /**
     * @return bool
     */
    public final function condition() : bool
    {
        return $this->match() && $this->getCurrentModel() !== false;
    }

    /**
     * Handle group
     *
     * @return void
     */
    public function handle()
    {
        Step::setModel($this->getStepping());
        $model = $this->getCurrentModel();

        $this->update->isHandled = false;
        try
        {
            foreach($this->list() as $handler)
            {
                if($handler instanceof Closure)
                {
                    $handler = $handler();
                }

                if($handler === null)
                {
                    continue;
                }

                if(!is_a($handler, UpdateHandling::class, true))
                {
                    throw new \TypeError("Expected [".UpdateHandling::class."], given [". (is_string($handler) ? $handler : get_class($handler)) ."]");
                }

                if(is_string($handler))
                {
                    if(is_a($handler, MiddleAction::class, true))
                    {
                        $handler = $handler::make();
                    }
                    $handler = new $handler;
                }

                $this->update->isHandled = true;
                $handler->handleUpdate($this->update);

                if($this->update->isHandled)
                {
                    break;
                }
            }
        }
        catch(StopHandlingException $stop)
        {
            // Nothing
        }
        catch(CancelHandlingException $cancel)
        {
            return;
        }
        catch(RepeatHandlingException $repeat)
        {
            $this->handle();
            return;
        }

        $this->final();
    }

    /**
     * Get list of handling
     *
     * @return UpdateHandling[]
     */
    public function list() : array
    {
        return [
            $this->step(),
        ];
    }

    /**
     * Get current step
     *
     * @return ?StepHandlerPipe
     */
    public function step()
    {
        if($stepping = $this->getStepping())
        {
            return new StepHandlerPipe($stepping);
        }

        return null;
    }

    /**
     * Get callback query control handler
     *
     * @param string $class
     * @return CallbackControlHandler
     */
    public function callback(string $class)
    {
        return new CallbackControlHandler($class);
    }

    /**
     * Get inline query control handler
     *
     * @param string $class
     * @return InlineControlHandler
     */
    public function inline(string $class)
    {
        return new InlineControlHandler($class);
    }

    /**
     * Get update handler for handling after a middle actions handled
     *
     * @param string $class
     * @param string $method
     * @param mixed  ...$args
     * @return MiddleActionHandledUpdateHandling
     */
    public function afterMiddles(string $class, string $method, ...$args)
    {
        return new MiddleActionHandledUpdateHandling(null, null, $class, $method, ...$args);
    }

    /**
     * Final work
     *
     * @return void
     */
    public function final()
    {
        if($model = $this->getCurrentModel())
        {
            $model->save();
        }
    }

}
