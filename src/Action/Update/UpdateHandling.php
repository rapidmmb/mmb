<?php

namespace Mmb\Action\Update;

use Mmb\Core\Updates\Update;

interface UpdateHandling
{

    public function handleUpdate(Update $update);

}
