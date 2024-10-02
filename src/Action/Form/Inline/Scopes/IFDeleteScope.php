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
                fn(Input\ConfirmInput $input) => $input
                    ->setup($this->prompt, $this->confirm)
                    ->when(isset($this->cancel))
                    ->cancelKey($this->cancel)
            )
        ;
    }

}
