<?php

namespace Mmb\Action\Road;

use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Section;
use Mmb\Core\Updates\Update;

/**
 * @template T of Sign
 */
abstract class Station extends Section
{

    public function __construct(
        public readonly Road $road,

        /**
         * @var T|Sign $sign
         */
        public readonly Sign $sign,

        Update               $update = null,
    )
    {
        parent::__construct($update);
    }

    /**
     * Event on registering an inline action
     *
     * @param InlineRegister $register
     * @return void
     */
    protected function onInitializeInlineRegister(InlineRegister $register)
    {
        $register->before(
            function(InlineAction $inline)
            {
                $inline->withOn('@', $this->road, 'curStation', ...$this->road->getWith());
            }
        );

        parent::onInitializeInlineRegister($register);
    }

}