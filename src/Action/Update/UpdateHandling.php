<?php

namespace Mmb\Action\Update;

use Mmb\Context;
use Mmb\Core\Updates\Update;

interface UpdateHandling
{

    public function handleUpdate(Context $context, Update $update);

}
