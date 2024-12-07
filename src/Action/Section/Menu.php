<?php

namespace Mmb\Action\Section;

use Closure;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Traits\Macroable;
use Mmb\Action\Contracts\Menuable;
use Mmb\Action\Filter\Filter;
use Mmb\Action\Filter\Filterable;
use Mmb\Action\Filter\FilterRule;
use Mmb\Action\Filter\HasEventFilter;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;

class Menu extends InlineAction implements Menuable
{
    use Macroable, Filterable, HasEventFilter;
    use Traits\CustomizesKeyboard;


    protected array $keyInitializer = [];

    protected array $keyHeader = [];

    protected array $keyFooter = [];

    /**
     * Set/Add menu key
     *
     * If store() is enabled, this values will save to user table and load with next update.
     * Otherwise, this values is not saving and menu key will reload from your codes.
     *
     * @param array|Closure $key
     * @param string        $name
     * @param bool          $fixed
     * @param bool          $exclude
     * @return $this
     */
    public function schema(array|Closure $key, string $name = 'main', bool $fixed = false, bool $exclude = false)
    {
        $this->keyInitializer[] = new MenuKeyGroup($this, $key, $name, $fixed, $exclude);
        return $this;
    }

    /**
     * Set/Add menu fixed key
     *
     * @param array|Closure $key
     * @param string        $name
     * @return $this
     */
    public function schemaFixed(array|Closure $key, string $name = 'main')
    {
        return $this->schema($key, $name, true);
    }

    /**
     * Set/Add menu that not included in loading menu
     *
     * @param array|Closure $key
     * @param string        $name
     * @return $this
     */
    public function schemaExcluded(array|Closure $key, string $name = 'main')
    {
        return $this->schema($key, $name, exclude: true);
    }

    /**
     * Set menu header key
     *
     * This values always load from your codes.
     *
     * @param array|Closure $key
     * @param string        $name
     * @param bool          $exclude
     * @return $this
     */
    public function header(array|Closure $key, string $name = 'main', bool $exclude = false)
    {
        $this->keyHeader[] = new MenuKeyGroup($this, $key, $name, true, $exclude);
        return $this;
    }

    /**
     * Set menu footer key
     *
     * This values always load from your codes.
     *
     * @param array|Closure $key
     * @param string        $name
     * @param bool          $exclude
     * @return $this
     */
    public function footer(array|Closure $key, string $name = 'main', bool $exclude = false)
    {
        $this->keyFooter[] = new MenuKeyGroup($this, $key, $name, true, $exclude);
        return $this;
    }

    /**
     * Create a key
     *
     * @param       $text
     * @param null  $action
     * @param mixed ...$args
     * @return MenuKey
     */
    public function key($text, $action = null, ...$args)
    {
        return new MenuKey($this, $text, $action, $args);
    }

    /**
     * Create a key to invoke another class
     *
     * @param        $text
     * @param string $class
     * @param string $method
     * @param        ...$args
     * @return MenuKey
     */
    public function keyFor($text, string $class, string $method = 'main', ...$args)
    {
        return $this->key($text)->invoke($class, $method, ...$args);
    }

    /**
     * Paginator row
     *
     * @param $paginator
     * @return array
     */
    public function paginateRow($paginator, $action = 'page')
    {
        $row = [];

        if ($paginator instanceof LengthAwarePaginator)
        {
            $row[] = $this->key("<<", $action, 1);

            for (
                $i = max($paginator->currentPage() - 3, 1);
                $i <= $paginator->currentPage() + 3 && $i <= $paginator->lastPage();
                $i++
            )
            {
                $row[] = $this->key(
                    $paginator->currentPage() == $i ? "[$i]" : "$i",
                    $action,
                    $i
                );
            }

            $row[] = $this->key(">>", $action, $paginator->lastPage());
        }
        elseif ($paginator instanceof Paginator)
        {
            $row[] = $this->key("<<", $action, max($paginator->currentPage() - 1, 1));
            $row[] = $this->key(
                ">>", $action, $paginator->hasMorePages() ? $paginator->currentPage() + 1 : $paginator->currentPage()
            );
        }
        elseif ($paginator instanceof CursorPaginator)
        {
            if ($paginator->hasMorePages())
            {
                $row[] = $this->key("...", $action, $paginator->nextCursor()->encode());
            }
        }
        else
        {
            throw new \InvalidArgumentException(
                sprintf("Paginate row don't support paginator of type [%s]", typeOf($paginator))
            );
        }

        return $row;
    }

    protected array $ifScopes = [];

    public function hasMoreIfScope(string $name)
    {
        return !isset($this->ifScopes[$name]);
    }

    public function setIfScope(string $name)
    {
        $this->ifScopes[$name] = true;
    }

    public function removeIfScope(string $name)
    {
        unset($this->ifScopes[$name]);
    }

    /**
     * Loop each of items
     *
     * @template T
     * @param \Traversable<mixed,T>  $items
     * @param Closure(T $item): void $callback
     * @return $this
     */
    public function foreach(iterable $items, Closure $callback)
    {
        foreach ($items as $item)
        {
            $callback($item);
        }

        return $this;
    }

    /**
     * Loop each of items when loading
     *
     * @template T
     * @param Closure|\Traversable<mixed,T> $items
     * @param Closure(T $item): void        $callback
     * @return $this
     */
    public function foreachLoading(Closure|iterable $items, Closure $callback)
    {
        return $this->loading(fn() => $this->foreach(value($items), $callback));
    }


    protected bool $store = false;

    /**
     * Enable storing mode
     *
     * @return $this
     */
    public function store()
    {
        $this->store = true;
        return $this;
    }


    protected array $onActions = [];

    /**
     * Fire action when another action invoked
     *
     * @param string|array|FilterRule $actionName
     * @param mixed                   $action
     * @return $this
     */
    public function on(string|array|FilterRule $actionName, $action = null)
    {
        if ($actionName instanceof FilterRule)
        {
            $this->addFilterEvent($actionName, new ActionCallback($action));
            return $this;
        }

        if (is_array($actionName))
        {
            foreach ($actionName as $name => $value)
            {
                $this->on($name, $value);
            }
        }
        else
        {
            $this->onActions[$actionName] = new ActionCallback($action);
        }

        return $this;
    }

    /**
     * Add event for regex pattern text message
     *
     * @param string $pattern
     * @param        $action
     * @param int    $pass
     * @return $this
     */
    public function onRegex(string $pattern, $action, int $pass = -2)
    {
        return $this->on(
            Filter::make()->regex($pattern, $pass, ''),
            $action
        );
    }

    protected $else = null;

    /**
     * Fire action when user send another messages
     *
     * @param $action
     * @return $this
     */
    public function else($action)
    {
        $this->else = new ActionCallback($action);
        return $this;
    }

    /**
     * Fire else updates
     *
     * @param Update $update
     * @return bool
     */
    public function fireElse(Update $update)
    {
        // Find filter events
        if ($this->getMatchedFilter($update, $action, $value))
        {
            $this->fireAction($action, $update, [$value]);
            return true;
        }

        // Else action
        if (isset($this->else))
        {
            [$ok, $passed, $value] = $this->passFilter($this-> $update);
            if (!$ok)
            {
                return $passed;
            }

            $this->fireAction($this->else, $update, $this->passFilterResult ? [$value] : []);
            return true;
        }

        return false;
    }

    /**
     * Save the action using message
     *
     * @param Message $message
     * @return void
     */
    protected function saveMenuAction(Message $message)
    {
        $this->saveAction();
    }

    protected $message;

    /**
     * Set message
     *
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    protected $responseUsing;

    /**
     * Set response callback
     *
     * @param Closure(array $args): ?Message $callback
     * @return $this
     */
    public function responseUsing(Closure $callback)
    {
        $this->responseUsing = $callback;
        return $this;
    }

    /**
     * Send menu as message
     *
     * @param       $message
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     */
    public function send($message = null, array $args = [], ...$namedArgs)
    {
        $this->makeReady();
        $message ??= value($this->message);

        return tap(
            $this->context->update->getChat()->sendMessage($message, $args + $namedArgs + ['key' => $this->cachedKey]),
            function($message)
            {
                if ($message)
                {
                    $this->saveMenuAction($message);
                }
            }
        );
    }

    /**
     * Reply menu as message
     *
     * @param       $message
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     */
    public function reply($message = null, array $args = [], ...$namedArgs)
    {
        $this->makeReady();
        $message ??= value($this->message);

        return tap(
            $this->context->message->replyMessage($message, $args + $namedArgs + ['key' => $this->cachedKey]),
            function($message)
            {
                if ($message)
                {
                    $this->saveMenuAction($message);
                }
            }
        );
    }

    /**
     * Send menu as message
     *
     * @param       $message
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     */
    public function response($message = null, array $args = [], ...$namedArgs)
    {
        $this->makeReady();
        $message ??= value($this->message);

        if (is_array($message))
            $args = $message + $args;
        elseif (!is_null($message))
            $args = ['text' => $message] + $args;

        return tap(
            ($this->responseUsing ?? $this->context->update->response(...))($args + $namedArgs + ['key' => $this->cachedKey]),
            function($message)
            {
                if ($message)
                {
                    $this->saveMenuAction($message);
                }
            }
        );
    }


    public ?array $cachedKey             = null;
    public ?array $cachedActions         = null;
    public ?array $cachedStorableActions = null;

    protected function makeReadyThis()
    {
        parent::makeReadyThis();
        $this->makeReadyKey();
    }

    private function makeReadyKey(array $storeActions = null)
    {
        $this->cachedKey = [];
        $this->cachedActions = [];
        $this->cachedStorableActions = [];

        if ($storeActions !== null)
        {
            $this->cachedActions = array_replace($this->cachedActions, $storeActions);
        }

        $this->makeReadyKeyGroup($this->keyHeader, false);
        $this->makeReadyKeyGroup($this->keyInitializer, $this->store, $storeActions !== null);
        $this->makeReadyKeyGroup($this->keyFooter, false);
    }

    private function makeReadyKeyGroup(array $group, bool $store = true, bool $skipStorable = false)
    {
        /** @var MenuKeyGroup $keyGroup */
        foreach ($group as $keyGroup)
        {
            $storable = $store && !$keyGroup->fixed && !$keyGroup->exclude;

            // If storable, skip
            if ($storable && $skipStorable)
            {
                continue;
            }

            // Loading mode & Excluded groups
            if ($this->isLoading() && $keyGroup->exclude)
            {
                continue;
            }

            // Convert key items group to key array and actions
            [$key, $actions] = $keyGroup->normalizeKey($storable);

            // Save key and actions
            array_push($this->cachedKey, ...$key);
            $this->cachedActions = array_replace($this->cachedActions, $actions);

            // Save storable actions
            if ($storable)
            {
                $storableActions = array_map(fn(ActionCallback $action) => $action->toArray(), $actions);
                $this->cachedStorableActions = array_replace($this->cachedStorableActions, $storableActions);
            }
        }
    }

    public function makeReadyFromStore(array $actions)
    {
        if ($this->isReady)
        {
            return;
        }

        $actions = array_map(fn($array) => ActionCallback::fromArray($array), $actions);
        $this->makeReadyKey($actions);

        $this->isReady = true;
    }

    /**
     * Find action name from update
     *
     * @param Update $update
     * @return ?ActionCallback
     */
    public function findActionFrom(Update $update)
    {
        return $this->findActionFromString(MenuKey::findActionKeyFrom($update));
    }

    /**
     * Find action name from string action name
     *
     * @param string|null $actionKey
     * @return ?ActionCallback
     */
    public function findActionFromString(?string $actionKey)
    {
        $action = null;

        if ($actionKey !== null)
        {
            $action = $this->cachedActions[$actionKey] ?? null;
        }

        if ($action instanceof ActionCallback && $action->isNamed() && array_key_exists(
                $action->action, $this->onActions
            ))
        {
            $action = $this->onActions[$action->action]->with($action->defaultArgs);
        }

        return $action;
    }

    /**
     * Find action callable
     *
     * @param array  $action
     * @param Update $update
     * @return ?callable
     */
    public function findActionCallable(array $action, Update $update)
    {
        @[$callable, $args] = $action;
        $args ??= [];

        // Closure action: fn() => Something
        if ($callable instanceof Closure)
        {
            return [$callable, $args];
        }

        // Array action: [SomeSection::class, 'someMethod']
        elseif (is_array($callable))
        {
            [$class, $method] = $callable;
            $class = $class::make($update);

            return [$class, $method, $args];
        }


        if ($this->initializerClass)
        {
            return [$this->initializerClass, $callable, $args];
        }

        return null;
    }

    /**
     * Fire action
     *
     * @param ActionCallback|string $name
     * @param Update                $update
     * @param array                 $args
     * @return void
     */
    public function fireAction(ActionCallback|string $name, Update $update, array $args = [])
    {
        if (is_string($name))
        {
            $name = new ActionCallback($name);
        }

        $name->invoke(
            $this->initializerClass,
            $this->context,
            $args,
            [
                'sender' => $this,
            ]
        );
    }

    /**
     * Fire action for update
     *
     * @param Update $update
     * @return bool
     */
    public function fire(Update $update)
    {
        $action = $this->findActionFrom($update);

        if ($action !== null)
        {
            $this->fireAction($action, $update);
            return true;
        }

        return $this->fireElse($update);
    }

    /**
     * Handle update
     *
     * @param Update $update
     * @return bool
     */
    public function handle(Update $update)
    {
        $this->makeReady();

        return (bool) $this->fire($update);
    }


    protected $stepHandlerClass = MenuStepHandler::class;

    /**
     * @param MenuStepHandler $step
     * @return void
     */
    protected function saveToStep(InlineStepHandler $step)
    {
        parent::saveToStep($step);

        $step->actionMap = $this->cachedStorableActions ?: null;
    }

    /**
     * @param MenuStepHandler $step
     * @param Update          $update
     * @return void
     */
    protected function loadFromStep(InlineStepHandler $step, Update $update)
    {
        parent::loadFromStep($step, $update);

        $this->makeReadyFromStore($step->actionMap ?: []);
    }

    /**
     * Invoke the default response
     *
     * @return Message|null
     */
    public function invoke()
    {
        return $this->response();
    }

    public function addMenuSchema(array $key) : void
    {
        $this->schema($key);
    }

    public function createActionKey(string $text, Closure $callback)
    {
        return $this->key($text, $callback);
    }
}
