<?php

namespace Mmb\Action\Section;

use Illuminate\Support\Traits\Conditionable;
use Mmb\Action\Action;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\KeySchema\KeyboardInterface;
use Mmb\Support\KeySchema\KeyInterface;
use Mmb\Support\KeySchema\KeyUniqueData;
use Mmb\Support\KeySchema\ManagingKey;
use Mmb\Support\KeySchema\StaticIfScopes;
use Mmb\Support\KeySchema\SupportingKey;

class MenuKey implements KeyInterface
{
    use ManagingKey, SupportingKey;
    use Conditionable;

    protected ?ActionCallback $action = null;

    public function __construct(
        public Menu     $menu,
        protected       $text,
                        $action,
        array           $args = [],
        protected array $attrs = [],
    )
    {
        $this->text = trim($text);
        $this->action($action, ...$args);
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Checks the target class and method is allowed
     *
     * @return true
     */
    public function isAllowed()
    {
        if (is_array($this->action->action) && is_a($this->action->action[0], Action::class, true)) {
            return $this->action->action[0]::allowed(@$this->action->action[1]);
        } elseif (is_string($this->action->action)) {
            return $this->menu->getInitializer()[0]::allowed($this->action->action);
        }

        return true;
    }

    /**
     * Display key if target calling class and method are allowed
     *
     * @param string $scope
     * @return $this
     */
    public function ifAllowed(string $scope = '_')
    {
        return $this->if($this->isAllowed(), $scope);
    }

    /**
     * Display key if before conditions are false and target calling class and method are allowed
     *
     * @param string $scope
     * @return $this
     */
    public function elseifAllowed(string $scope = '_')
    {
        return $this->elseif($this->isAllowed(), $scope);
    }

}
