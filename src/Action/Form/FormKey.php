<?php

namespace Mmb\Action\Form;

use Illuminate\Support\Traits\Conditionable;
use Mmb\Action\Form\Actions\InputFillActionCallback;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;
use Mmb\Support\KeySchema\KeyInterface;
use Mmb\Support\KeySchema\ManagingKey;
use Mmb\Support\KeySchema\SupportingKey;

class FormKey implements KeyInterface
{
    use ManagingKey, SupportingKey;
    use Conditionable;

    protected ?ActionCallback $action = null;

    public function __construct(
        public string $text,
        mixed         $action = null,
        array         $args = [],
    )
    {
        $this->text = trim($this->text);
        $this->action($action, ...$args);
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Make new form key
     *
     * @param string $text
     * @param        $value
     * @return static
     */
    public static function make(string $text, $value = null): static
    {
        $key = new static($text);

        if (func_num_args() > 1) {
            $key->value($value);
        }

        return $key;
    }

    /**
     * Make new form key with custom action
     *
     * @param string $text
     * @param        $action
     * @return FormKey
     */
    public static function makeAction(string $text, $action): static
    {
        return (new static($text))->action($action);
    }

    /**
     * Set the value that the input should replace it.
     *
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->action(new InputFillActionCallback($value));
        return $this;
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
                'text' => $this->text,
                'requestContact' => true,
            ],
            'location' => [
                'text' => $this->text,
                'requestLocation' => true,
            ],
            'user'     => [
                'text' => $this->text,
                'requestUser' => $this->typeOptions,
            ],
            'users'    => [
                'text' => $this->text,
                'requestUsers' => $this->typeOptions,
            ],
            'chat'     => [
                'text' => $this->text,
                'requestChat' => $this->typeOptions,
            ],
            'poll'     => [
                'text' => $this->text,
                'requestPoll' => $this->typeOptions,
            ],
            default    => [
                'text' => $this->text,
            ],
        };
    }

}
