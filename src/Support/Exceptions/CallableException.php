<?php

namespace Mmb\Support\Exceptions;

use Mmb\Core\Updates\Update;

interface CallableException
{

    public function invoke(Update $update);

}
