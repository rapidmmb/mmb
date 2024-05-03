<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\ResourceMaker;

class ResourceOrderModule extends ResourceSimpleFilterModule
{

    public function __construct(ResourceMaker $maker, string $name)
    {
        parent::__construct($maker, $name);

        $this->addAsc(fn() => __('mmb.resource.order.oldest'), 'created_at');
        $this->addDesc(fn() => __('mmb.resource.order.newest'), 'created_at');
    }

    public function addOrder($label, string $orderBy, bool $asc = true, bool $default = false, bool $visible = true)
    {
        if($asc)
            return $this->add($label, fn($query) => $query->orderBy($orderBy), $default, $visible);
        else
            return $this->add($label, fn($query) => $query->orderByDesc($orderBy), $default, $visible);
    }

    public function addAsc($label, string $orderBy, bool $default = false)
    {
        return $this->addOrder($label, $orderBy, true, $default);
    }

    public function addDesc($label, string $orderBy, bool $default = false)
    {
        return $this->addOrder($label, $orderBy, false, $default);
    }


    public function getDefaultMessage()
    {
        return __('mmb.resource.order.message');
    }

}
