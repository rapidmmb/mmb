<?php

namespace Mmb\Action\Section\Controllers;

trait InlineControl
{

    private QueryMatcher $_inlineMatcher;

    /**
     * Get inline matcher
     *
     * @return QueryMatcher
     */
    public function getInlineMatcher()
    {
        return $this->_inlineMatcher ?? QueryMatcher::makeFrom('inline', $this, 'onInline');
    }

    /**
     * Inline pattern maker
     *
     * @param QueryMatcher $matcher
     * @return void
     */
    public function onInline(QueryMatcher $matcher)
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
    // public function keyInline(string $text, ...$args)
    // {
    //     $query = $this->getInlineMatcher()->makeQuery($args);
    //
    //     return [
    //         'text' => $text,
    //         'data' => $query,
    //     ];
    // }

}
