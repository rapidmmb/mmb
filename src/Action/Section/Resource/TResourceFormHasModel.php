<?php

namespace Mmb\Action\Section\Resource;

use Illuminate\Database\Eloquent\Model;
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
        $this->theModel = $this->getRecordFrom($id);
        $this->setDynArgs(record: $this->theModel);
        $this->form($form);
    }

    public function withRecord(Model $record)
    {
        $this->theModel = $record;
        return $this;
    }

}
