<?php

namespace Mmb\Action\Section;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Action;
use Mmb\Action\Memory\StepMemory;
use Mmb\Action\Section\Attributes\FixedDialog;
use Mmb\Core\Client\Exceptions\TelegramException;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\KeySchema\KeyInterface;
use Mmb\Support\KeySchema\KeyUniqueData;

class Dialog extends Menu
{

    protected ?string $use = null;

    /**
     * Use the model to save dialog
     *
     * @param string $model
     * @return $this
     */
    public function use(string $model)
    {
        $this->use = $model;
        return $this;
    }

    /**
     * Get used model
     *
     * @return ?string
     */
    public function getUse(): ?string
    {
        return $this->use;
    }

    /**
     * @return bool
     */
    public function isFixed(): bool
    {
        return !isset($this->use);
    }

    protected FixedDialog $fixedDialog;

    /**
     * Get FixedDialog value
     *
     * @return FixedDialog|null
     */
    public function getFixedValue()
    {
        if (!isset($this->fixedDialog)) {
            if (!$this->isFixed()) {
                return null;
            }

            $fixedDialog = $this->initializerClass::getMethodAttributeOf($this->initializerMethod, FixedDialog::class);

            if (!$fixedDialog) {
                throw new \TypeError("Fixed dialog required to define with #[FixedDialog] before the method.");
            }

            return $this->fixedDialog = $fixedDialog;
        }

        return $this->fixedDialog;
    }


    protected Model $dialogRecord;

    /**
     * Get used model
     *
     * @return ?Model
     */
    public function getUsed(): ?Model
    {
        if ($this->isFixed())
            return null;

        return $this->dialogRecord ??= $this->use::create([
            'user_id' => $this->context->bot->guard()->user()->getKey(),
        ]);
    }

    protected $stepHandlerClass = DialogStepHandler::class;

    /**
     * @param Message $message
     * @return void
     */
    protected function saveMenuAction(Message $message)
    {
        if ($this->isFixed())
            return;

        $this->dialogRecord->update([
            'target' => $this->toStep(),
        ]);
    }

    /**
     * Save the action
     *
     * @return void
     */
    protected function saveAction()
    {
        if ($this->isFixed())
            return;

        $memory = new StepMemory();
        $this->toStep()?->save($memory);

        $this->dialogRecord->update([
            'target' => $memory->all(),
        ]);
    }

    public function makeKey(string $text, Closure $callback, array $args): DialogKey
    {
        return new DialogKey($this, $text, $callback, $args);
    }

    /**
     * Create a key
     *
     * @param $text
     * @param $action
     * @param ...$args
     * @return DialogKey
     */
    public function key($text, $action = null, ...$args): DialogKey
    {
        return new DialogKey($this, $text, $action, $args);
    }

    /**
     * Create a key with same action and id value
     *
     * @param        $text
     * @param string $action
     * @return DialogKey
     */
    public function keyId($text, string $action): DialogKey
    {
        return $this->key($text, $action)->id($action);
    }

    protected bool $autoReload = false;
    protected bool $shouldReload = false;

    /**
     * Enable auto reloading (reload dialog after each action)
     *
     * @return $this
     */
    public function autoReload()
    {
        $this->autoReload = true;
        return $this;
    }

    /**
     * @param ActionCallback|string $name
     * @param Update $update
     * @param array $args
     * @return void
     */
    public function fireAction(ActionCallback|string $name, Update $update, array $args = [])
    {
        if ($this->autoReload) {
            $this->shouldReload = true;
        }

        parent::fireAction($name, $update, $args);

        if ($this->autoReload && $this->shouldReload) {
            $this->reload();
        }
    }

    public function fireFixedClickedKey(string $action, array $args): bool
    {
        if ($clicked = $this->findKeyActionUsingData(KeyUniqueData::makeDialog($action))) {
            $this->fireAction($clicked, $this->context->update, $args);
            return true;
        }

        return false;
    }

    /**
     * Cancel reload dialog
     *
     * @return $this
     */
    public function cancelReload()
    {
        $this->shouldReload = false;
        return $this;
    }

    /**
     * Force reload and edit message
     *
     * @return Message
     */
    public function reload($message = null, array $args = [], ...$namedArgs)
    {
        $dialogRegister = $this->initializerClass->reloadInlineRegister($this);

        if (!$this->isFixed()) {
            $dialogRegister->inlineAction->dialogModel = $this->dialogRecord;
        }

        return $dialogRegister->register()->editResponse($message, $args, ...$namedArgs);
    }

    /**
     * Send or edit dialog
     *
     * @param       $message
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     */
    public function response($message = null, array $args = [], ...$namedArgs)
    {
        return isset($this->responseUsing) || !$this->context->update->callbackQuery ?
            parent::response($message, $args, $namedArgs) :
            $this->editResponse($message, $args, ...$namedArgs);
    }


    /**
     * Send menu as message
     *
     * @param       $message
     * @param array $args
     * @param       ...$namedArgs
     * @return Message|null
     */
    public function editResponse($message = null, array $args = [], ...$namedArgs)
    {
        $this->makeReady();
        $message ??= value($this->message);

        try {
            return tap(
                $this->context->message->editText($message, $args + $namedArgs + ['key' => $this->toKeyboardArray()]),
                function ($message) {
                    if ($message) {
                        $this->saveMenuAction($message);
                    }
                },
            );
        } catch (TelegramException $exception) {
            if (str_contains($exception->getMessage(), 'Bad Request: message is not modified')) {
                return $this->context->message;
            }

            throw $exception;
        }
    }

    /**
     * Answer the callback query and run callbacks value
     *
     * @param $response
     * @param ...$callbacks
     * @return $this
     */
    public function answer($response, ...$callbacks)
    {
        $this->context->update->tell($response);

        foreach ($callbacks as $callback) {
            $callback();
        }

        return $this;
    }

}
