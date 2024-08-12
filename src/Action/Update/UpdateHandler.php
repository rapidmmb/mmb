<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Action;
use Mmb\Action\Memory\Step;
use Mmb\Action\Memory\StepHandlerPipe;
use Mmb\Action\Middle\MiddleAction;
use Mmb\Action\Middle\MiddleActionHandledUpdateHandling;
use Mmb\Action\Section\Controllers\CallbackControlHandler;
use Mmb\Action\Section\Controllers\InlineControlHandler;
use Mmb\Core\Bot;
use Mmb\Support\Db\ModelFinder;
use Mmb\Support\Step\Stepping;

abstract class UpdateHandler extends Action
{

    public abstract function handle(HandlerFactory $handler);

}
