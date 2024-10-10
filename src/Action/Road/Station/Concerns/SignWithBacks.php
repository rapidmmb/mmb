<?php

namespace Mmb\Action\Road\Station\Concerns;

use closure;
use Mmb\Action\Contracts\Menuable;
use Mmb\Action\Form\Input;
use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;

/**
 * @method $this backKey(Closure|false $callback, int $x = 50, int $y = 200)
 * @method $this backKeyDefault(int $x = 50, int $y = 200)
 * @method $this backKeyAction(Closure $action)
 * @method $this backKeyLabel(Closure $callback)
 * @method $this backKeyLabelUsing(Closure $callback)
 * @method $this backKeyLabelPrefix(string|Closure $string)
 * @method $this backKeyLabelSuffix(string|Closure $string)
 */
trait SignWithBacks
{

    protected function bootSignWithBacks()
    {
        $this->defineKey('backKey', 'footer', 50, 200);
    }

    protected function onDefaultBackKey(Menuable $menuable, Station $station)
    {
        return $menuable->createActionKey(
            $this->getDefinedLabel($station, 'backKeyLabel'),
            fn () => $this->fireBy($station, 'backKeyAction')
        );
    }

    protected function onBackKeyLabel()
    {
        return __('mmb::road.back');
    }

    protected function onBackKeyAction()
    {
        $this->road->fireBack();
    }

}