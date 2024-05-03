<?php

namespace Mmb\Action\Form\Inline\Scopes;

use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Inline\InlineFormScope;
use Mmb\Action\Form\Input;

class IFDeleteScope extends InlineFormScope
{

    public function __construct(
        public ?string $prompt = null,
        public ?string $confirm = null,
        public ?string $cancel = null,
    )
    {
    }

    public function apply(InlineForm $form)
    {
        $form
            ->input('confirm',
                fn(Input $input) => $input
                    ->prompt($this->prompt ?? __('mmb.form.scopes.delete.prompt'))
                    ->add($this->confirm ?? __('mmb.form.scopes.delete.confirm'), true)
            )
        ;

        if(isset($this->cancel))
        {
            $form->form->cancelKey($this->cancel);
        }
    }

}
