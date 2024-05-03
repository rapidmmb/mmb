<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;

class ResourceFormModule extends ResourceModule
{
    use TResourceFormableModule;

    protected $then = [];

    public function then(Closure $callback)
    {
        $this->then[] = $callback;
        return $this;
    }

    protected $thenBack;

    public function thenBack($action)
    {
        $this->thenBack = $action;
        return $this;
    }

    protected $keyLabel;

    public function keyLabel($label)
    {
        $this->keyLabel = $label;
        return $this;
    }

    public function getKeyLabel()
    {
        return $this->valueOf($this->keyLabel ?? $this->getDefaultKeyLabel());
    }

    protected function getDefaultKeyLabel()
    {
        return "-";
    }

    public function getThenBackAction()
    {
        if(isset($this->thenBack))
        {
            return $this->thenBack;
        }

        return $this->back;
    }


    public function resetChunk()
    {
        $this->setMy('#', null);
    }

    public function onLeave()
    {
        $this->resetChunk();
    }


    public function main(...$args)
    {
        $this->inlineForm('form')->request();
    }

    protected $inlineAliases = [
        'form' => 'form',
    ];

    public function form(InlineForm $form)
    {
        $this->initForm($form);
        $form->form->cancelKey($this->getBackLabel());
        $form->cancel(fn() => $this->fireBack());
        $form->finish(function() use($form)
        {
            $attributes = $this->getFormAttributes($form);

            $this->formFinished($form->form, $attributes);
            $this->formFinishedBack();
        });
    }

    protected function formFinished(Form $form, array $attributes)
    {
        $this->resetChunk();
        foreach($this->then as $callback)
        {
            $this->valueOf($callback, attributes: $attributes, form: $form);
        }
    }

    protected function formFinishedBack()
    {
        $this->fireAction($this->getThenBackAction());
    }

}
