<?php

namespace Mmb\Action\Road\Station;

use Mmb\Action\Road\Station\Concerns\SignWithMenuCustomizing;
use Mmb\Action\Road\WeakSign;

class SimpleFilterSign extends WeakSign
{
    use SignWithMenuCustomizing,
        Concerns\SignWithQueryFilters,
        Concerns\SignWithMessage,
        Concerns\SignWithBacks;

}