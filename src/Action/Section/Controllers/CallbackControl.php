<?php

namespace Mmb\Action\Section\Controllers;

trait CallbackControl
{

    private QueryMatcher $_callbackMatcher;

    /**
     * Get callback matcher
     *
     * @return QueryMatcher
     */
    public function getCallbackMatcher()
    {
        return $this->_callbackMatcher ?? QueryMatcher::makeFrom('callback', $this, 'onCallback');
    }

    /**
     * Callback pattern maker
     *
     * @param QueryMatcher $matcher
     * @return void
     */
    public function onCallback(QueryMatcher $matcher)
    {
        $matcher->autoMatch($this);
    }

    /**
     * Make an inline key
     *
     * @param string $text
     * @param        ...$args
     * @return array
     */
    public function keyInline(string $text, ...$args)
    {
        $query = $this->getCallbackMatcher()->makeQuery(...$args);

        return [
            'text' => $text,
            'data' => $query,
        ];
    }

}
