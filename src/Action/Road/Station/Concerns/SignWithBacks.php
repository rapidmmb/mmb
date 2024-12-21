<?php

namespace Mmb\Action\Road\Station\Concerns;

use closure;
use Mmb\Action\Road\Station;

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

    /**
     * @var Station\Words\SignKey<$this>
     */
    public Station\Words\SignKey $back;

    protected function bootSignWithBacks()
    {
        $this->back = Station\Words\SignKey::make($this);
        $this->back->in('footer', 50, 200);
        $this->back->label(__('mmb::road.back'));
        $this->back->action(function () {
            $this->road->fireBack();
        });
    }

//    protected function onDefaultBackKey(Menuable $menuable, Station $station)
//    {
//        return $menuable->createActionKey(
//            $this->getDefinedLabel($station, 'backKeyLabel'),
//            fn () => $this->fireBy($station, 'backKeyAction')
//        );
//    }

}