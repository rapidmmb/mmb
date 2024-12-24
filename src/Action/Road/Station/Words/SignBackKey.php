<?php

namespace Mmb\Action\Road\Station\Words;

/**
 * @template T
 * @extends SignKey<T>
 */
class SignBackKey extends SignKey
{

    protected function boot()
    {
        parent::boot();
        $this->in('footer', 50, 200);
        $this->label(__('mmb::road.back'));
        $this->action(function () {
            $this->road->fireBack();
        });
    }

}