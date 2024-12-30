<?php

namespace Mmb\Support\KeySchema;

use Mmb\Support\Action\ActionCallback;

trait ManagingKey
{

    protected ?ActionCallback $action = null;

    /**
     * Set key click action
     *
     * @param $action
     * @param ...$args
     * @return $this
     */
    public function action($action, ...$args)
    {
        if ($action instanceof ActionCallback && $args) {
            $action = (clone $action)->addArgs($args);
        }

        $this->action = match (true) {
            is_null($action)                  => null,
            $action instanceof ActionCallback => $action,
            default                           => new ActionCallback($action, $args),
        };
        return $this;
    }

    /**
     * Set key click action to calling a class method
     *
     * @param string $class
     * @param string $method
     * @param        ...$args
     * @return $this
     */
    public function invoke(string $class, string $method = 'main', ...$args)
    {
        return $this->action([$class, $method], ...$args);
    }

    /**
     * Get action
     *
     * @return ?ActionCallback
     */
    public function toAction(): ?ActionCallback
    {
        return $this->action;
    }


    protected bool $isVisible = true;

    /**
     * Set key visibility
     *
     * @param $condition
     * @return $this
     */
    public function visible($condition = true)
    {
        $this->isVisible = (bool)value($condition);
        return $this;
    }

    /**
     * Set key hidden
     *
     * @param $condition
     * @return $this
     */
    public function hidden($condition = true)
    {
        $this->isVisible = !value($condition);
        return $this;
    }

    /**
     * Visible key if condition is true
     *
     * @param        $condition
     * @param string $scope
     * @return $this
     */
    public function visibleIf($condition, string $scope = '_')
    {
        $this->isVisible = false;

        if (value($condition)) {
            StaticIfScopes::setIfScope($scope);
            $this->isVisible = true;
        } else {
            StaticIfScopes::removeIfScope($scope);
        }

        return $this;
    }

    /**
     * Visible key if before conditions are false and this condition is true.
     *
     * @param        $condition
     * @param string $scope
     * @return $this
     */
    public function visibleElseif($condition, string $scope = '_')
    {
        $this->isVisible = false;

        if (StaticIfScopes::isNotSetIfScope($scope)) {
            if (value($condition)) {
                StaticIfScopes::setIfScope($scope);
                $this->isVisible = true;
            }
        }

        return $this;
    }

    /**
     * Visible key if before conditions are false.
     *
     * @param string $scope
     * @return $this
     */
    public function visibleElse(string $scope = '_')
    {
        $this->isVisible = false;

        if (StaticIfScopes::isNotSetIfScope($scope)) {
            StaticIfScopes::setIfScope($scope);
            $this->isVisible = true;
        }

        return $this;
    }

    /**
     * Visible key if target calling class and method are allowed
     *
     * @param string $scope
     * @return $this
     */
    public function visibleIfAllowed(string $scope = '_')
    {
        return $this->visibleIf($this->isAllowed(), $scope);
    }

    /**
     * Visible key if before conditions are false and target calling class and method are allowed
     *
     * @param string $scope
     * @return $this
     */
    public function visibleElseifAllowed(string $scope = '_')
    {
        return $this->visibleElseif($this->isAllowed(), $scope);
    }

    /**
     * Check key is visible
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    
    protected bool $display = true;

    /**
     * Display key if condition is true.
     *
     * @param        $condition
     * @param string $scope
     * @return $this
     */
    public function if($condition, string $scope = '_')
    {
        $this->display = false;

        if (value($condition)) {
            StaticIfScopes::setIfScope($scope);
            $this->display = true;
        } else {
            StaticIfScopes::removeIfScope($scope);
        }

        return $this;
    }

    /**
     * Display key if before conditions are false and this condition is true.
     *
     * @param        $condition
     * @param string $scope
     * @return $this
     */
    public function elseif($condition, string $scope = '_')
    {
        $this->display = false;

        if (StaticIfScopes::isNotSetIfScope($scope)) {
            if (value($condition)) {
                StaticIfScopes::setIfScope($scope);
                $this->display = true;
            }
        }

        return $this;
    }

    /**
     * Display key if before conditions are false.
     *
     * @param string $scope
     * @return $this
     */
    public function else(string $scope = '_')
    {
        $this->display = false;

        if (StaticIfScopes::isNotSetIfScope($scope)) {
            StaticIfScopes::setIfScope($scope);
            $this->display = true;
        }

        return $this;
    }

    /**
     * Checks if the key can display in list.
     *
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return $this->display;
    }

    protected bool $isIncluded = true;

    /**
     * Don't associate the key.
     *
     * This option will make faster menu in store mode, but key action will not work as well!
     * You can use this option on store mode, and use {@see Menu::onRegex()} method to find clicked key.
     *
     * @return $this
     */
    public function exclude()
    {
        $this->isIncluded = false;
        return $this;
    }

    /**
     * Checks key is included to associating.
     *
     * @return bool
     */
    public function isIncluded(): bool
    {
        return $this->isIncluded && $this->action !== null;
    }

}