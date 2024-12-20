<?php

namespace Mmb\Action\Section;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Mmb\Action\Action;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\KeySchema\KeyboardInterface;
use Mmb\Support\KeySchema\KeyInterface;
use Mmb\Support\KeySchema\KeyUniqueData;
use Mmb\Support\KeySchema\StaticIfScopes;

class MenuKey implements KeyInterface
{
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


    protected string $type = 'text';
    protected $typeOptions = null;

    /**
     * Set key type to contact type
     *
     * @return $this
     */
    public function requestContact()
    {
        $this->type = 'contact';
        return $this;
    }

    /**
     * Set key type to location type
     *
     * @return $this
     */
    public function requestLocation()
    {
        $this->type = 'location';
        return $this;
    }

    /**
     * Set key type to request user type
     *
     * @param int $id
     * @param ...$namedArgs
     * @return $this
     */
    public function requestUser(int $id, ...$namedArgs)
    {
        $this->type = 'user';
        $this->typeOptions = $namedArgs + ['id' => $id];
        return $this;
    }

    /**
     * Set key type to request users type
     *
     * @param int $id
     * @param int $max
     * @param     ...$namedArgs
     * @return $this
     */
    public function requestUsers(int $id, int $max = 10, ...$namedArgs)
    {
        $this->type = 'users';
        $this->typeOptions = $namedArgs + ['id' => $id, 'max' => $max];
        return $this;
    }

    /**
     * Set key type to request chat type
     *
     * @param int $id
     * @param ...$namedArgs
     * @return $this
     */
    public function requestChat(int $id, ...$namedArgs)
    {
        $this->type = 'chat';
        $this->typeOptions = $namedArgs + ['id' => $id];
        return $this;
    }

    /**
     * Set key type to request poll
     *
     * @param ...$namedArgs
     * @return $this
     */
    public function requestPoll(...$namedArgs)
    {
        $this->type = 'poll';
        $this->typeOptions = $namedArgs;
        return $this;
    }

    public function getUniqueData(KeyboardInterface $base): ?string
    {
        return match ($this->type) {
            'text'     => KeyUniqueData::makeText($this->text),
            'contact'  => KeyUniqueData::makeContact(),
            'location' => KeyUniqueData::makeLocation(),
            'user'     => KeyUniqueData::makeRequestUser($this->typeOptions['id']),
            'users'    => KeyUniqueData::makeRequestUsers($this->typeOptions['id']),
            'chat'     => KeyUniqueData::makeRequestChat($this->typeOptions['id']),
            'poll'     => KeyUniqueData::makePoll(),
            default    => null,
        };
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
     * Get attributes
     *
     * @return array
     */
    public function toArray(): array
    {
        return match ($this->type) {
            'contact'  => [
                'text' => $this->getText(),
                'requestContact' => true,
            ],
            'location' => [
                'text' => $this->getText(),
                'requestLocation' => true,
            ],
            'user'     => [
                'text' => $this->getText(),
                'requestUser' => $this->typeOptions,
            ],
            'users'    => [
                'text' => $this->getText(),
                'requestUsers' => $this->typeOptions,
            ],
            'chat'     => [
                'text' => $this->getText(),
                'requestChat' => $this->typeOptions,
            ],
            'poll'     => [
                'text' => $this->getText(),
                'requestPoll' => $this->typeOptions,
            ],
            default    => [
                'text' => $this->getText(),
            ],
        };
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

}
