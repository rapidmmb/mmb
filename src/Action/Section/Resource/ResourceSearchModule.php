<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;
use Spatie\Searchable\Search;

class ResourceSearchModule extends ResourceModule
{

    protected $key = 'name';

    public function key($key)
    {
        $this->key = $key;
        return $this;
    }

    public function by($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getKey()
    {
        return $this->key ?? 'name';
    }


    protected $message;

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->valueOf($this->message) ?? __('mmb::resource.search.message');
    }


    protected $algo = 'Auto';

    public function simple()
    {
        $this->algo = 'Simple';
        return $this;
    }

    public function query(Closure $callback)
    {
        $this->algo = $callback;
        return $this;
    }

    protected function applyQuery(Builder $query, $string)
    {
        switch($this->algo)
        {
            case 'Auto':


            case 'Simple':
                $like = preg_replace('/^|$|[\s!@#$%^&*\-+\/?.,_:;]+/', '%', $string);
                return $query->where($this->getKey(), 'like', $like);

            default:
                return $this->valueOf($this->algo, $query, $string, key: $this->getKey());
        }
    }

    // public function getAutoQuery()
    // {
    //     (new Search())->search()
    // }

    protected $enableAllKey;
    protected $allKeyLabel;

    public function enableAllKey($condition = true)
    {
        $this->enableAllKey = $condition;
        return $this;
    }

    public function allKeyEnable($condition = true)
    {
        $this->enableAllKey = $condition;
        return $this;
    }

    public function allKeyLabel($label)
    {
        $this->allKeyLabel = $label;

        if(!isset($this->enableAllKey))
        {
            $this->allKeyEnable();
        }

        return $this;
    }

    public function isAllKeyEnabled()
    {
        return $this->valueOf($this->enableAllKey);
    }

    public function getAllKeyLabel()
    {
        return $this->valueOf($this->allKeyLabel) ?? __('mmb::resource.search.all_key_label');
    }


    protected $keyLabel;
    protected $keyLabelSearching;

    public function keyLabel($label)
    {
        $this->keyLabel = $label;
        return $this;
    }

    public function keyLabelSearching($label)
    {
        $this->keyLabelSearching = $label;
        return $this;
    }

    public function getKeyLabel()
    {
        if($this->getMy('q') !== null && isset($this->keyLabelSearching))
        {
            return $this->valueOf($this->keyLabelSearching, query: $this->getMy('q'));
        }

        return $this->valueOf($this->keyLabel ?? __('mmb::resource.search.label'));
    }



    public function main()
    {
        $this->inlineForm('searchForm')->request();
    }

    protected $inlineAliases = [
        'searchForm' => 'searchForm',
    ];

    public function searchForm(InlineForm $form)
    {
        $form
            ->input(
                'query',
                fn(Input $input) => $input
                    ->textSingleLine()
                    ->prompt($this->getMessage(...))
                    ->when($this->isAllKeyEnabled(), fn() => $input->add(
                        $input->keyAction(
                            $this->getAllKeyLabel(),
                            function()
                            {
                                $this->setMy('q', null);
                                $this->fireBack();
                            },
                        )
                    ))
                    ->options($this->keyToOptions($input, 'back'))
            )
            ->finish(function(Form $form)
            {
                $this->setMy('q', $form->query);
                $this->fireBack();
            })
        ;

        $form->form->disableCancelKey();
    }

    public function bootLinked(ResourceModule $module)
    {
        if($module instanceof ResourceListModule)
        {
            $module
                ->filter(
                    function(Builder $query)
                    {
                        $string = $this->getMy('q');
                        if($string !== null && $string !== '')
                        {
                            return $this->applyQuery($query, $string);
                        }

                        return $query;
                    }
                );
        }
    }

}
