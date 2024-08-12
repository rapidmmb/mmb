<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Section\ResourceMaker;

class ResourceCreateModule extends ResourceFormModule
{
    public function __construct(
        ResourceMaker $maker,
        string $name,
        protected string $model,
    )
    {
        parent::__construct($maker, $name);
    }

    protected $created;

    public function created($action)
    {
        $this->created = $action;
        return $this;
    }

    public function createdOpenInfo(string $name)
    {
        return $this->created(fn($record) => $this->fireAction($name, [$record]));
    }

    protected $creating;

    public function creating(Closure $callback)
    {
        $this->creating = $callback;
        return $this;
    }

    protected $keyLabel;

    public function keyLabel($label)
    {
        $this->keyLabel = $label;
        return $this;
    }

    public function getDefaultKeyLabel()
    {
        return __('mmb.resource.create.key_label');
    }

    public function getThenBackAction()
    {
        if(isset($this->created))
        {
            return $this->created;
        }

        return parent::getThenBackAction();
    }


    public function request()
    {
        $this->fireAction($this->name);
    }

    public function requestChunk($chunk)
    {
        $this->setCurrentChunk($chunk);
        $this->request();
    }


    protected $createdModel;

    protected function formFinished(Form $form, array $attributes)
    {
        if(isset($this->creating))
            $this->createdModel = $this->valueOf($this->creating, $attributes);
        else
            $this->createdModel = $this->model::create($attributes);

        parent::formFinished($form, $attributes);
    }

    protected function formFinishedBack()
    {
        $this->fireAction($this->getThenBackAction(), [$this->createdModel], openArgs: []);
    }

}
