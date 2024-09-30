<?php

namespace Mmb\Action\Section\Resource;

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


    public function request($model)
    {
        $this->fireAction($this->name, [$model]);
    }

    public function requestChunk($model, $chunk)
    {
        $this->setCurrentChunk($chunk);
        $this->request($model);
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
        $this->theModel->delete();

        parent::formFinished($form, $attributes);
    }

}
