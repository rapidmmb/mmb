<?php

namespace Mmb\Support\Exceptions;

use Mmb\Context;
use Mmb\Core\Updates\Update;

interface CallableException
{

    public function invoke(Context $context);

}
