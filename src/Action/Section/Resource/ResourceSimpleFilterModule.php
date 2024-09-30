<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\ResourceMaker;

class ResourceSimpleFilterModule extends ResourceModule
{

    public function __construct(ResourceMaker $maker, string $name)
    {
        parent::__construct($maker, $name);
    }

    protected $filters = [];
    protected $defaultFilter;

    public function add($label, Closure $callback, bool $default = false, $visible = true)
    {
        $this->filters[] = [
            'label'    => $label,
            'callback' => $callback,
            'visible'  => $visible,
        ];

        if($default)
        {
            $this->defaultFilter = array_key_last($this->filters);
        }

        return $this;
    }

    public function addNone($label = null, bool $default = null)
    {
        if(!isset($default) && !isset($this->defaultFilter))
        {
            $default = true;
        }

        return $this->add($label ?? fn() => __('mmb::resource.default.none'), fn($query) => $query, $default ?? false);
    }

    public function reset()
    {
        $this->filters = [];
        $this->defaultFilter = null;
        return $this;
    }

    protected $toggle = false;

    public function toggle($condition = true)
    {
        $this->toggle = $condition;
        return $this;
    }

    public function isToggleMode()
    {
        return $this->valueOf($this->toggle);
    }


    protected $message;

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->valueOf($this->message) ?? $this->getDefaultMessage();
    }

    public function getDefaultMessage()
    {
        return __('mmb::resource.filter.message');
    }


    protected $label;
    protected $labelPrefix    = [];
    protected $labelSuffix    = [];
    protected $labelCurPrefix = [];
    protected $labelCurSuffix = [];

    public function label($label)
    {
        $this->label = $label;
        return $this;
    }

    public function labelAuto()
    {
        return $this->label(fn($label) => $label);
    }

    public function prefix($prefix)
    {
        $this->labelPrefix[] = $prefix;
        return $this;
    }

    public function getPrefix($label, $key)
    {
        return implode(array_map(fn($x) => $this->valueOf($x, $label, $key), $this->labelPrefix));
    }

    public function suffix($suffix)
    {
        $this->labelSuffix[] = $suffix;
        return $this;
    }

    public function getSuffix($label, $key)
    {
        return implode(array_map(fn($x) => $this->valueOf($x, $label, $key), $this->labelSuffix));
    }

    public function prefixCurrent($prefix)
    {
        $this->labelCurPrefix[] = $prefix;
        return $this;
    }

    public function getPrefixCurrent($label, $key)
    {
        return implode(array_map(fn($x) => $this->valueOf($x, $label, $key), $this->labelCurPrefix));
    }

    public function suffixCurrent($suffix)
    {
        $this->labelCurSuffix[] = $suffix;
        return $this;
    }

    public function getSuffixCurrent($label, $key)
    {
        return implode(array_map(fn($x) => $this->valueOf($x, $label, $key), $this->labelCurSuffix));
    }


    protected $keyLabel;
    protected $keyLabelPrefix;
    protected $keyLabelSuffix;

    public function keyLabel($keyLabel)
    {
        $this->keyLabel = $keyLabel;
        return $this;
    }

    public function keyLabelAuto()
    {
        return $this->keyLabel(fn($keyLabel) => $keyLabel);
    }

    public function keyLabelPrefix($prefix)
    {
        $this->keyLabelPrefix = $prefix;
        return $this;
    }

    public function keyLabelSuffix($suffix)
    {
        $this->keyLabelSuffix = $suffix;
        return $this;
    }

    public function getLabel($filterKey)
    {
        if($filter = @$this->filters[$filterKey ?? $this->getFilter()])
        {
            $label = $this->valueOf($filter['label']);
        }
        else $label = "---";

        $prefix = '';
        $suffix = '';
        if($filterKey == $this->getFilter())
        {
            $prefix .= $this->getPrefixCurrent($label, $filterKey);
            $suffix .= $this->getSuffixCurrent($label, $filterKey);
        }
        $prefix .= $this->getPrefix($label, $filterKey);
        $suffix .= $this->getSuffix($label, $filterKey);

        return $prefix . $this->valueOf(
                $this->label ?? $this->getLabelDefault($label, $filterKey), $label, $filterKey
            ) . $suffix;
    }

    protected function getLabelDefault($label, $key)
    {
        return $label;
    }

    public function getKeyLabel($filterKey = null)
    {
        if($filter = @$this->filters[$filterKey ?? $this->getFilter()])
        {
            $label = $this->valueOf($filter['label']);
        }
        else $label = "---";

        return $this->valueOf($this->keyLabelPrefix, $label, $filterKey) .
            $this->valueOf($this->keyLabel ?? $this->getKeyLabelDefault($label, $filterKey), $label, $filterKey) .
            $this->valueOf($this->keyLabelSuffix, $label, $filterKey);
    }

    protected function getKeyLabelDefault($label, $key)
    {
        return $label;
    }

    protected $keys = [
        'top'    => [],
        'bottom' => [],
        'back'   => [],
    ];

    public function addTopKey(
        $label, $action = null, string $name = null, ?int $x = null, ?int $y = null, $condition = true,
    )
    {
        $this->addKey($label, 'top', $action, $name, $x, $y, $condition);
        return $this;
    }

    public function moveTopKey(string $name, int $x = null, int $y = null)
    {
        $this->moveKey($name, 'top', $x, $y);
        return $this;
    }

    public function addBottomKey(
        $label, $action = null, string $name = null, ?int $x = null, ?int $y = null, $condition = true,
    )
    {
        $this->addKey($label, 'bottom', $action, $name, $x, $y, $condition);
        return $this;
    }

    public function moveBottomKey(string $name, int $x = null, int $y = null)
    {
        $this->moveKey($name, 'bottom', $x, $y);
        return $this;
    }

    protected $perLine;

    public function perLine($count)
    {
        $this->perLine = $count;
        return $this;
    }


    public function getDefaultFilter()
    {
        return $this->defaultFilter ?? array_key_first($this->filters);
    }

    public function getFilter()
    {
        return $this->getMy('f', fn() => $this->getDefaultFilter());
    }

    protected function setFilter($filter)
    {
        $this->setMy('f', $filter);
    }

    public function main()
    {
        if($this->isToggleMode())
        {
            $filter = $this->getFilter() + 1;
            if(!isset($this->filters[$filter]))
                $filter = 0;

            $this->setFilter($filter);
            $this->fireBack();
        }
        else
        {
            $this->menu('selectMenu')->send();
        }
    }

    protected $inlineAliases = [
        'selectMenu' => 'selectMenu',
    ];

    public function selectMenu(Menu $menu)
    {
        $menu
            ->schema($this->keyToSchema($menu, 'top'))
            ->schema(
                function() use ($menu)
                {
                    $key = [];
                    foreach($this->filters as $i => $filter)
                    {
                        if($this->valueOf(@$filter['visible']))
                        {
                            $key[] = $menu->key($this->getLabel($i), 'select', $i);
                        }
                    }
                    yield from array_chunk($key, $this->valueOf($this->perLine ?? 1));
                }
            )
            ->schema($this->keyToSchema($menu, 'bottom'))
            ->schema($this->keyToSchema($menu, 'back'))
            ->message($this->getMessage(...))
            ->on(
                'select', function($i)
            {
                $this->setFilter($i);
                $this->fireBack();
            }
            );
    }

    protected function bootLinked(ResourceModule $module)
    {
        if($module instanceof ResourceListModule)
        {
            $module
                ->filter(
                    function(Builder $query)
                    {
                        if($filter = @$this->filters[$this->getFilter()])
                        {
                            return $filter['callback']($query);
                        }

                        return $query;
                    }
                );
        }
    }

}
