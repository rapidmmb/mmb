<?php

namespace Mmb\Action\Section\Attributes;

use Attribute;
use Mmb\Action\Action;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Section\Controllers\Attributes\OnCallback;
use Mmb\Action\Section\Controllers\QueryMatcher;
use Mmb\Action\Section\Controllers\QueryMatchPattern;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Section;
use Mmb\Context;
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
        $pattern = ($this->full ? '' : QueryMatcher::getClassId($class) . ':') . $this->pattern;

        if (!isset(static::$matchers[$pattern]))
        {
            (static::$matchers[$pattern] = QueryMatcher::make('callback'))->match($pattern);
        }

        return static::$matchers[$pattern];
    }

    public function fire(Context $context, QueryMatchPattern $pattern, string $class, string $method)
    {
        [$args, $within] = Caller::splitArguments($pattern->getVisibleMatches());

        $dialog = new Dialog($context);
        $dialog->loadFromData($within);

        /** @var Action $action */
        $action = $class::makeByContext($context);
        $dialogLoad = $action->loadInlineRegister($dialog, $method);
        $dialogLoad->register();

        $dialog->makeReady();
        // Dialog::handleFrom();
        $dialog->fireAction(
            $dialog->findActionFromString('D' . $pattern->getMatch('_action')),
            $context->update,
            $args
        );
    }

}
