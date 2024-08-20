<?php

namespace Mmb\Action\Section\Attributes;

use Attribute;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Section\Controllers\Attributes\OnCallback;
use Mmb\Action\Section\Controllers\QueryMatcher;
use Mmb\Action\Section\Controllers\QueryMatchPattern;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Section;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

#[Attribute(Attribute::TARGET_METHOD)]
class FixedDialog extends OnCallback
{

    public function __construct(string $pattern, bool $full = false)
    {
        parent::__construct($pattern . ':{_action:any}', $full, null);
    }

    protected static $matchers = [];

    /**
     * @return QueryMatcher
     */
    public function getMatcher(string $class, string $method)
    {
        if (!isset(static::$matchers[$this->pattern]))
        {
            $pattern = ($this->full ? '' : QueryMatcher::getClassId($class) . ':') . $this->pattern;
            (static::$matchers[$this->pattern] = QueryMatcher::make('callback'))->match($pattern);
        }

        return static::$matchers[$this->pattern];
    }

    public function fire(QueryMatchPattern $pattern, string $class, string $method)
    {
        [$args, $within] = Caller::splitArguments($pattern->getVisibleMatches());

        $dialog = new Dialog;
        $dialog->loadFromData($within);

        /** @var InlineLoadRegister $dialogLoad */
        $dialogLoad = $class::make()->loadInlineRegister($dialog, $method);
        $dialogLoad->register();

        $dialog->makeReady();
        // Dialog::handleFrom();
        $dialog->fireAction(
            $dialog->findActionFromString('D' . $pattern->getMatch('_action')),
            app(Update::class),
            $args
        );
    }

}
