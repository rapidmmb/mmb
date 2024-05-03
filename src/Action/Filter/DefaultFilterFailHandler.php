<?php

namespace Mmb\Action\Filter;

use Mmb\Core\Updates\Update;

class DefaultFilterFailHandler
{

    public function handle(FilterFailException $exception, Update $update)
    {
        $update->response($exception->description);
    }

}
