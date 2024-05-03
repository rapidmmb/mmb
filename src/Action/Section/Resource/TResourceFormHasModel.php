<?php

namespace Mmb\Action\Section\Resource;

use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Section\ResourceMaker;

trait TResourceFormHasModel
{
    use TResourceHasModel;

    public function __construct(
        ResourceMaker $maker,
        string $name,
        protected string $model,
    )
    {
        parent::__construct($maker, $name);
    }

    public function main(...$args)
    {
        $id = $this->getIdFrom($args[0]);
        $this->inlineForm('modelForm', id: $id)->request();
    }

    public function getInlineAliases() : array
    {
        return [
            'modelForm' => 'modelForm',
        ];
    }

    protected $theModel;

    public function modelForm(InlineForm $form, $id)
    {
        $this->theModel = $this->getModelFrom($id);
        $this->setDynArgs(model: $this->theModel);
        $this->form($form);
    }

}
