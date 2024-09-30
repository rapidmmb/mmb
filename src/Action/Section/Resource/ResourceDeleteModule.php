<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;
use Mmb\Action\Section\ResourceMaker;

class ResourceDeleteModule extends ResourceFormModule
{
    use TResourceFormHasModel;

    protected function getDefaultKeyLabel()
    {
        return __('mmb::resource.delete.key_label');
    }

    protected $deleting;

    public function deleting(Closure $callback)
    {
        $this->deleting = $callback;
        return $this;
    }


    protected $message;

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->valueOf($this->message) ?? __('mmb::resource.delete.message');
    }


    protected $confirm;

    public function confirm($confirm)
    {
        $this->confirm = $confirm;
        return $this;
    }

    public function getConfirm()
    {
        return $this->valueOf($this->confirm) ?? __('mmb::resource.delete.confirm');
    }


    public function request($record)
    {
        $this->fireAction($this->name, [$record]);
    }

    public function requestChunk($record, $chunk)
    {
        $this->setCurrentChunk($chunk);
        $this->request($record);
    }

    protected function initForm(InlineForm $form)
    {
        parent::initForm($form);

        $form->input('confirm', function(Input $input)
        {
            $input
                ->add($this->getConfirm(), true)
                ->prompt($this->getMessage());
        });
    }

    protected function formFinished(Form $form, array $attributes)
    {
        if (isset($this->deleting))
        {
            $this->valueOf($this->deleting, $this->theModel);
        }
        else
        {
            $this->theModel->delete();
        }

        parent::formFinished($form, $attributes);
    }

}
