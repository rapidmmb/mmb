<?php

namespace Mmb\Action\Road;

use Closure;
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

    /**
     * Fire a sign event
     *
     * @param string|array|Closure $event
     * @param                      ...$args
     * @return mixed
     */
    public function fireSign(string|array|Closure $event, ...$args)
    {
        return $this->sign->fire($event, ...$args, ...$this->getDynamicArgs());
    }


    protected array $dynamicArgs = [];

    /**
     * Merge dynamic arguments
     *
     * @param array $args
     * @return $this
     */
    public function mergeDynamicArgs(array $args)
    {
        $this->dynamicArgs = array_replace($this->dynamicArgs, $args);
        return $this;
    }

    /**
     * Get list of dynamic arguments
     *
     * @return array
     */
    protected function getDynamicArgs(): array
    {
        return [
            'station' => $this,
            ...$this->dynamicArgs,
        ];
    }

}