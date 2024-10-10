<?php

namespace Mmb\Action\Road\Customize;

use Closure;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\WeakSign;

class InputCustomizer
{
    use Concerns\SchemaCustomizes;

    public function __construct(
        protected WeakSign                $sign,
        protected readonly FormCustomizer $formCustomizer,
        public string                     $name,
        public string                     $chunk,
        public int                        $order,
        public Closure                    $callback,
    )
    {
    }

    public function order(int $order)
    {
        $this->order = $order;
        return $this;
    }

    public function orderFirst()
    {
        return $this->order($this->formCustomizer->_first_order_flag++);
    }

    public function orderLast()
    {
        return $this->order($this->formCustomizer->_last_order_flag--);
    }

    public function chunk(string $name)
    {
        $this->chunk = $name;
        return $this;
    }

    public function rename(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function isRtl(): ?bool
    {
        return $this->rtl ?? $this->formCustomizer->isRtl();
    }

    public function init(Station $station, InlineForm $form, Input $input)
    {
        $station->fireSignAs($this->sign, $this->callback, $input, form: $form);

        $schema = $this->fetchMultipleSchema($station, [$this, $this->formCustomizer], 'header', $input);
        $input->addHeader($schema);

        $schema = $this->fetchMultipleSchema($station, [$this, $this->formCustomizer], 'body', $input);
        $input->add($schema);

        $schema = $this->fetchMultipleSchema($station, [$this, $this->formCustomizer], 'footer', $input);
        $input->addFooter($schema);
    }

}