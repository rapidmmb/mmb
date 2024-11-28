<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\Concerns\AdvancedRule;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;

class FilterMessage extends BeMessage
{
    use AdvancedRule;

    public function __construct(
        $messageError = null,
    )
    {
        parent::__construct($messageError);
    }

    /**
     * Filter the message type
     *
     * @param string|array $type
     * @param mixed        $error
     * @return FilterMessage
     */
    public function type(string|array $type, mixed $error = null)
    {
        return $this->should('type', 'in', (array) $type);
    }

    /**
     * Filter the message global type
     *
     * @param string|array $type
     * @param mixed        $error
     * @return FilterMessage
     */
    public function globalType(string|array $type, mixed $error = null)
    {
        return $this->should('globalType', 'in', (array) $type);
    }

    /**
     * Message should not forward
     *
     * @param mixed|null $error
     * @return FilterMessage
     */
    public function notForwarded(mixed $error = null)
    {
        return $this->should('isForwarded', 'is', false, $error ?? __('mmb::filter.not-forward'));
    }

    /**
     * Message should forward
     *
     * @param mixed|null $error
     * @return FilterMessage
     */
    public function forwarded(mixed $error = null)
    {
        return $this->should('isForwarded', 'is', true, $error ?? __('mmb::filter.should-forward'));
    }

    public function media(mixed $error = null)
    {
        return $this->should('media', 'is not', null, $error ?? __('mmb::filter.media'));
    }

    public function pass(Update $update, &$value)
    {
        parent::pass($update, $value);

        $this->passAdvanced($update->message->forwa);
    }

}