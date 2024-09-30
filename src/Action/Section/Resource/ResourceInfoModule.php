<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Illuminate\Support\Str;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\ResourceMaker;

class ResourceInfoModule extends ResourceModule
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

    protected $keys = [
        'head' => [],
        'main' => [],
        'back' => [],
    ];

    public function addMainKey($label, $action = null, int $x = null, int $y = null, $condition = true)
    {
        $this->addKey($label, 'main', $action, null, $x, $y, $condition);
        return $this;
    }

    public function addHeadKey($label, $action = null, ?int $x = 0, int $y = null, string $name = null, $condition = true)
    {
        $this->addKey($label, 'head', $action, $name, $x, $y, $condition);
        return $this;
    }



    protected $schemas = [];

    public function schema($key)
    {
        $this->schemas[] = $key;
        return $this;
    }


    public function deletable(Closure $init = null, string $name = 'delete', ?int $x = 50, ?int $y = 0)
    {
        $this->maker->module($delete = new ResourceDeleteModule($this->maker, $name, $this->model));

        $delete->back(fn($record) => $this->fireAction($this->name, [$record]));
        $delete->thenBack(fn() => $this->fireBack());

        if($init) $init($delete);

        $this->addHeadKey(
            fn() => $delete->getKeyLabel(),
            fn($record) => $delete->request($record),
            x: $x,
            y: $y,
        );

        return $this;
    }

    public function softDeletable(Closure $init = null, string $name = 'soft-delete', ?int $x = 50, ?int $y = 0)
    {
        $this->maker->module($softDelete = new ResourceSoftDeleteModule($this->maker, $name, $this->model));

        $softDelete->back(fn($record) => $this->fireAction($this->name, [$record]));
        $softDelete->thenBack(fn() => $this->fireBack());

        if($init) $init($softDelete);

        $this->addHeadKey(
            fn() => $softDelete->getKeyLabel(),
            fn($record) => $softDelete->request($record),
            x: $x,
            y: $y,
        );

        return $this;
    }


    protected $editableName;

    public function editable(Closure $init = null, string $name = 'edit', ?int $x = 150, ?int $y = 0)
    {
        $this->editableName = $name;
        $this->maker->module($edit = new ResourceEditModule($this->maker, $name, $this->model));

        $edit->editedOpenInfo($this->name);

        if($init) $init($edit);

        $this->addHeadKey(
            fn() => $edit->getKeyLabel(),
            fn($record) => $edit->request($record),
            x: $x,
            y: $y,
        );

        return $this;
    }

    public function editableSingle($label, string $input, null|Closure|bool $right = null, null|Closure|bool $left = null)
    {
        return $this->schema(fn(Menu $menu, $record) => [
            [
                $left ? $menu->key($left === true ? Str::limit($record->$input, 50) : $this->valueOf($left, @$record->$input)) : null,
                $menu->key($this->valueOf($label), fn() => $this->maker->getModule($this->editableName)->requestChunk($record, $input)),
                $right ? $menu->key($right === true ? Str::limit($record->$input, 50) : $this->valueOf($right, @$record->$input)) : null,
            ],
        ]);
    }

    protected $message;

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->valueOf($this->message ?? __('mmb::resource.info.message'));
    }



    public function main($id)
    {
        $id = $this->getIdFrom($id);
        $this->menu('infoMenu', id: $id)->send();
    }

    protected $inlineAliases = [
        'infoMenu' => 'infoMenu',
    ];

    public function infoMenu(Menu $menu, $id)
    {
        $record = $this->getRecordFrom($id);
        $this->setDynArgs(record: $record);

        $menu
            ->schema($this->keyToSchema($menu, 'head', $record))
            ->schema($this->keyToSchema($menu, 'main', $record))
        ;

        foreach($this->schemas as $schema)
        {
            $menu->schema(fn() => $this->valueOf($schema, $menu, $record));
        }

        $menu
            ->schema($this->keyToSchema($menu, 'back'))
            ->message($this->getMessage(...))
            ->on('back', fn() => $this->fireBack())
        ;
    }

}
