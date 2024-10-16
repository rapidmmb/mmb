<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;

class ResourceSoftDeleteModule extends ResourceFormModule
{
    use TResourceFormHasModel;

    protected function getDefaultKeyLabel()
    {
        return $this->theModel->trashed() ?
            __('mmb::resource.soft_delete.trashed_key_label') :
            __('mmb::resource.delete.key_label');
    }


    protected $deleting;

    public function deleting(Closure $callback)
    {
        $this->deleting = $callback;
        return $this;
    }

    protected $trashing;

    public function trashing(Closure $callback)
    {
        $this->trashing = $callback;
        return $this;
    }

    protected $restoring;

    public function restoring(Closure $callback)
    {
        $this->restoring = $callback;
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
        return $this->valueOf($this->message) ?? __('mmb::resource.soft_delete.trash_message');
    }


    protected $deleteMessage;

    public function deleteMessage($message)
    {
        $this->deleteMessage = $message;
        return $this;
    }

    public function getDeleteMessage()
    {
        return $this->valueOf($this->deleteMessage) ?? __('mmb::resource.soft_delete.delete_message');
    }


    protected $confirm;

    public function confirm($confirm)
    {
        $this->confirm = $confirm;
        return $this;
    }

    public function getConfirm()
    {
        return $this->valueOf($this->confirm) ?? __('mmb::resource.soft_delete.trash_confirm');
    }

    protected $deleteKey;

    public function deleteKey($text)
    {
        $this->deleteKey = $text;
        return $this;
    }

    public function getDeleteKey()
    {
        return $this->valueOf($this->deleteKey) ?? __('mmb::resource.soft_delete.delete_key');
    }

    protected $deleteConfirm;

    public function deleteConfirm($confirm)
    {
        $this->deleteConfirm = $confirm;
        return $this;
    }

    public function getDeleteConfirm()
    {
        return $this->valueOf($this->deleteConfirm) ?? __('mmb::resource.soft_delete.delete_confirm');
    }

    protected $restoreKey;

    public function restoreKey($text)
    {
        $this->restoreKey = $text;
        return $this;
    }

    public function getRestoreKey()
    {
        return $this->valueOf($this->restoreKey) ?? __('mmb::resource.soft_delete.restore_key');
    }

    protected $viewMessage;

    public function viewMessage($text)
    {
        $this->viewMessage = $text;
        return $this;
    }

    public function getViewMessage()
    {
        return $this->valueOf($this->viewMessage) ?? __('mmb::resource.soft_delete.view_message');
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
            if (!$this->theModel->trashed())
            {
                $input
                    ->add($this->getConfirm(), true)
                    ->add($this->getDeleteKey(), 'force')
                    ->prompt($this->getMessage());
            }
            else
            {
                $input
                    ->add($this->getDeleteKey(), 'force')
                    ->add($this->getRestoreKey(), 'restore')
                    ->prompt($this->getViewMessage());
            }
        });

        $form->input('delete_confirm', function (Input $input, Form $form)
        {
            if ($form->confirm !== 'force')
            {
                $form->next();
            }

            $input
                ->add($this->getDeleteConfirm(), true)
                ->prompt($this->getDeleteMessage());
        });
    }

    protected function formFinished(Form $form, array $attributes)
    {
        if ($form->confirm === 'force')
        {
            if (isset($this->deleting))
            {
                $this->valueOf($this->deleting, $this->theModel);
            }
            else
            {
                $this->theModel->forceDelete();
            }
        }
        elseif ($form->confirm === 'restore')
        {
            if (isset($this->restoring))
            {
                $this->valueOf($this->restoring, $this->theModel);
            }
            else
            {
                $this->theModel->restore();
            }
        }
        else
        {
            if (isset($this->trashing))
            {
                $this->valueOf($this->trashing, $this->theModel);
            }
            else
            {
                $this->theModel->delete();
            }
        }

        parent::formFinished($form, $attributes);
    }

}
