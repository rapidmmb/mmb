<?php

namespace Mmb\Core\Traits;

trait ApiBotCallbacks
{

    /**
     * Answer callback query
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return bool
     */
    public function answerCallback(array $args = [], ...$namedArgs)
    {
        return $this->request('answerCallbackQuery', $args + $namedArgs);
    }

}
