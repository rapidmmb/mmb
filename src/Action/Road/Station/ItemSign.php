<?php

namespace Mmb\Action\Road\Station;

use Mmb\Action\Road\Road;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\WeakSign;

class ItemSign extends WeakSign
{

    public function __construct(
        Road $road,
        protected WeakSign $sign,
        public object $record,
    )
    {
        parent::__construct($road);
    }

    /**
     * @var Words\SignKey<$this>
     */
    public Station\Words\SignKey $key;

    public bool $visible = true;

    public function visible(bool $value = true)
    {
        $this->visible = $value;
        return $this;
    }

    public function hidden(bool $value = true)
    {
        $this->visible = !$value;
        return $this;
    }


    public function getRoot(): Sign
    {
        return $this->sign->getRoot();
    }

}