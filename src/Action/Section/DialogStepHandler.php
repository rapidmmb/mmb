<?php

namespace Mmb\Action\Section;

use Mmb\Core\Updates\Update;

class DialogStepHandler extends MenuStepHandler
{

    public function handle(Update $update)
    {
        Dialog::handleFrom($this, $update);
    }

}
