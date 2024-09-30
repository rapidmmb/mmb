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

        $delete->back(fn($model) => $this->fireAction($this->name, [$model]));
        $delete->thenBack(fn() => $this->fireBack());

        if($init) $init($delete);

        $this->addHeadKey(
            fn() => $delete->getKeyLabel(),
            fn($model) => $delete->request($model),
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
        return $this->schema(fn(Menu $menu, $model) => [
            [
                $left ? $menu->key($left === true ? Str::limit($model->$input, 50) : $this->valueOf($left, @$model->$input)) : null,
                $menu->key($this->valueOf($label), fn() => $this->maker->getModule($this->editableName)->requestChunk($model, $input)),
                $right ? $menu->key($right === true ? Str::limit($model->$input, 50) : $this->valueOf($right, @$model->$input)) : null,
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
        $model = $this->getModelFrom($id);
        $this->setDynArgs(record: $model);

        $menu
            ->schema($this->keyToSchema($menu, 'head', $model))
            ->schema($this->keyToSchema($menu, 'main', $model))
        ;

        foreach($this->schemas as $schema)
        {
            $menu->schema(fn() => $this->valueOf($schema, $menu, $model));
        }

        $menu
            ->schema($this->keyToSchema($menu, 'back'))
            ->message($this->getMessage(...))
            ->on('back', fn() => $this->fireBack())
        ;
    }

}
