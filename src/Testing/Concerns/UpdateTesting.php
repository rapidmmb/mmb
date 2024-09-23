<?php

namespace Mmb\Testing\Concerns;

trait UpdateTesting
{

    public function setUpUpdateTesting()
    {
        app()->bind();
    }

}