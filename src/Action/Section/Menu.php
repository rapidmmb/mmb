<?php

namespace Mmb\Action\Section;

use Closure;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Traits\Macroable;
use Mmb\Action\Filter\Filter;
use Mmb\Action\Filter\Filterable;
use Mmb\Action\Filter\FilterRule;
use Mmb\Action\Filter\HasEventFilter;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\KeySchema\HasKeyboards;
use Mmb\Support\KeySchema\KeyboardInterface;
use Mmb\Support\KeySchema\KeyInterface;

class Menu extends InlineAction implements KeyboardInterface
{
    use Macroable, Filterable, HasEventFilter;
    use HasKeyboards;


    public function makeKey(string $text, Closure $callback, array $args): MenuKey
    {
        return new MenuKey($this, $text, $callback, $args);
    }

    /**
     * Create a key
     *
     * @param       $text
     * @param null $action
     * @param mixed ...$args
     * @return MenuKey
     */
    public function key($text, $action = null, ...$args): MenuKey
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
    public function keyFor($text, string $class, string $method = 'main', ...$args): MenuKey
    {
        return $this->key($text)->invoke($class, $method, ...$args);
    }

    /**
     * Paginator row
     *
     * @param $paginator
     * @return array
     * @deprecated
     */
    public function paginateRow($paginator, $action = 'page')
    {
        $row = [];

        if ($paginator instanceof LengthAwarePaginator) {
            $row[] = $this->key("<<", $action, 1);

            for (
                $i = max($paginator->currentPage() - 3, 1);
                $i <= $paginator->currentPage() + 3 && $i <= $paginator->lastPage();
                $i++
            ) {
                $row[] = $this->key(
                    $paginator->currentPage() == $i ? "[$i]" : "$i",
                    $action,
                    $i,
                );
            }

            $row[] = $this->key(">>", $action, $paginator->lastPage());
        } elseif ($paginator instanceof Paginator) {
            $row[] = $this->key("<<", $action, max($paginator->currentPage() - 1, 1));
            $row[] = $this->key(
                ">>", $action, $paginator->hasMorePages() ? $paginator->currentPage() + 1 : $paginator->currentPage(),
            );
        } elseif ($paginator instanceof CursorPaginator) {
            if ($paginator->hasMorePages()) {
                $row[] = $this->key("...", $action, $paginator->nextCursor()->encode());
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf("Paginate row don't support paginator of type [%s]", typeOf($paginator)),
            );
        }

        return $row;
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
     * @param mixed $action
     * @return $this
     */
    public function on(string|array|FilterRule $actionName, $action = null)
    {
        if ($actionName instanceof FilterRule) {
            $this->addFilterEvent($actionName, new ActionCallback($action));
            return $this;
        }

        if (is_array($actionName)) {
            foreach ($actionName as $name => $value) {
                $this->on($name, $value);
            }
        } else {
            $this->onActions[$actionName] = new ActionCallback($action);
        }

        return $this;
    }

    /**
     * Add event for regex pattern text message
     *
     * @param string $pattern
     * @param        $action
     * @param int|string $pass
     * @return $this
     */
    public function onRegex(string $pattern, $action, int|string $pass = '')
    {
        return $this->on(
            Filter::make()->regex($pattern, $pass, ''),
            $action,
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
    public function fireElse(Update $update): bool
    {
        if (isset($this->else)) {
            [$ok, $passed, $value] = $this->passFilter($this->context, $update);
            if (!$ok) {
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
            $this->context->update->getChat()->sendMessage($message, $args + $namedArgs + ['key' => $this->toKeyboardArray()]),
            function ($message) {
                if ($message) {
                    $this->saveMenuAction($message);
                }
            },
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
            $this->context->message->replyMessage($message, $args + $namedArgs + ['key' => $this->toKeyboardArray()]),
            function ($message) {
                if ($message) {
                    $this->saveMenuAction($message);
                }
            },
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
            ($this->responseUsing ?? $this->context->update->response(...))($args + $namedArgs + ['key' => $this->toKeyboardArray()]),
            function ($message) {
                if ($message) {
                    $this->saveMenuAction($message);
                }
            },
        );
    }


    protected function makeReadyThis()
    {
        parent::makeReadyThis();
        $this->makeReadyKeyboards($this->isCreating(), $this->store);
    }

    /**
     * Fire action
     *
     * @param ActionCallback|string $name
     * @param Update $update
     * @param array $args
     * @return void
     */
    public function fireAction(ActionCallback|string $name, Update $update, array $args = [])
    {
        if (is_string($name)) {
            $name = new ActionCallback($name);
        }

        $name->invoke(
            $this->initializerClass,
            $this->context,
            $args,
            [
                'sender' => $this,
            ],
        );
    }

    /**
     * Handle update
     *
     * @param Update $update
     * @return bool
     */
    public function handle(Update $update)
    {
        if ($clicked = $this->findClickedKeyAction($update)) {
            $this->fireAction($clicked, $update);
            return true;
        }

        if ($this->getMatchedFilter($update, $action, $value)) {
            $this->fireAction($action, $update, [$value]);
            return true;
        }

        return $this->fireElse($update);
    }


    protected $stepHandlerClass = MenuStepHandler::class;

    /**
     * @param MenuStepHandler $step
     * @return void
     */
    protected function saveToStep(InlineStepHandler $step)
    {
        parent::saveToStep($step);

        $step->storableKeyMap = $this->storableKeyMap ?: null;
    }

    /**
     * @param MenuStepHandler $step
     * @param Update $update
     * @return void
     */
    protected function loadFromStep(InlineStepHandler $step, Update $update)
    {
        parent::loadFromStep($step, $update);

        $this->loadStoredKeyboards($step->storableKeyMap ?: []);
    }

    public function restoreActionCallback(array $value): ?ActionCallback
    {
        return ActionCallback::fromArray($value);
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

}
