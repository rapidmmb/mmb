<?php

namespace Mmb\Action\Section\Resource;

use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Section\Menu;

interface IResourceListModifier
{

    public function applyModifier();

}
