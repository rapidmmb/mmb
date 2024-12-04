<?php

namespace Mmb\Action\Update;

use Mmb\Action\Action;

abstract class UpdateHandler extends Action
{

    public abstract function handle(HandlerFactory $handler);

}
