<?php

namespace Mmb\Core\Traits;

trait ApiBotInlines
{

    /**
     * Answer inline query
     *
     * @param array $args
     * @param       ...$namedArgs
     * @return bool
     */
    public function answerInlineQuery(array $args = [], ...$namedArgs)
    {
        return $this->request('answerInlineQuery', $args + $namedArgs);
    }

}
