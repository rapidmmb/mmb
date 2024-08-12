<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Section\ResourceMaker;

class ResourceEditModule extends ResourceFormModule
{
    use TResourceFormHasModel;

    protected $edited;

    public function edited($action)
    {
        $this->edited = $action;
        return $this;
    }

    public function editedOpenInfo(string $name)
    {
        return $this->edited(fn($model) => $this->fireAction($name, [$model]));
    }

    protected $editing;

    public function editing(Closure $callback)
    {
        $this->editing = $callback;
        return $this;
    }

    protected function getDefaultKeyLabel()
    {
        return __('mmb.resource.edit.key_label');
    }

    public function getThenBackAction()
    {
        if(isset($this->edited))
        {
            return $this->edited;
        }

        return parent::getThenBackAction();
    }


    public function request($record)
    {
        $this->fireAction($this->name, [$record]);
    }

    public function requestChunk($model, $chunk)
    {
        $this->setCurrentChunk($chunk);
        $this->request($model);
    }

    public function inputMessageOf(string $name, $message, ...$args)
    {
        return parent::inputMessageOf($name, $message, ...$args, old: $this->theModel->$name);
    }



    protected function formFinished(Form $form, array $attributes)
    {
        if(isset($this->editing))
            $this->valueOf($this->editing, $this->theModel, $attributes);
        else
            $this->theModel->update($attributes);

        parent::formFinished($form, $attributes);
    }

    protected function formFinishedBack()
    {
        $this->fireAction($this->getThenBackAction(), [$this->theModel], openArgs: []);
    }

}
