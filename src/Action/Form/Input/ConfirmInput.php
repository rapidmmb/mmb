<?php

namespace Mmb\Action\Form\Input;

use Mmb\Action\Form\Input;

class ConfirmInput extends Input
{

    protected ?string $promptMessage = null;

    protected ?string $confirmKey = null;

    /**
     * Set up the messages
     *
     * @param string|null $prompt
     * @param string|null $confirm
     * @return $this
     */
    public function setup(?string $prompt = null, ?string $confirm = null)
    {
        $this->promptMessage = $prompt;
        $this->confirmKey = $confirm;
        return $this;
    }


    protected function onInitializing()
    {
        parent::onInitializing();

        $this
            ->onlyOptions()
            ->disableIneffectiveKey()
            ->disableWithoutChangesKey();
    }

    protected function onInitialized()
    {
        parent::onInitialized();

        $this
            ->prompt($this->promptMessage ?? __('mmb::form.scopes.delete.prompt'))
            ->onlyOptions()
            ->add($this->confirmKey ?? __('mmb::form.scopes.delete.confirm'), true);
    }

}