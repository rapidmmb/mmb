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
                    ->onlyOptions()
                    ->add($this->confirm ?? __('mmb.form.scopes.delete.confirm'), true)
                    ->when(isset($this->cancel), fn() => $input->cancelKey($this->cancel))
            )
        ;
    }

}
