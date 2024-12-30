<?php

namespace Mmb\Action\Road\Customize;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\WeakSign;

class FormCustomizer
{
    use Concerns\SchemaCustomizes;

    public function __construct(
        protected WeakSign $sign,
    )
    {
    }

    public int $_first_order_flag = -1000;
    public int $_last_order_flag  = 1000;

    /**
     * @var InputCustomizer[]
     */
    protected array $inputs = [];

    public function insertInput(string $name, Closure $callback, ?string $chunk = null, int $order = 50)
    {
        return $this->inputs[] = new InputCustomizer($this->sign, $this, $name, $chunk ?? $name, $order, $callback);
    }

    public function removeInput(string $name)
    {
        $this->inputs = array_filter($this->inputs, fn ($input) => $input->name !== $name);
    }

    public function getInput(string $name): ?InputCustomizer
    {
        return Arr::first($this->inputs, fn ($input) => $input->name === $name);
    }


    public function init(InlineForm $form, ?array $chunks = null)
    {
        $form->disableCancelKey();

        /**
         * @var InputCustomizer $inputCustomizer
         */
        foreach (collect($this->inputs)->unique('name')->sortBy('order') as $inputCustomizer)
        {
            if (isset($chunks) && !in_array($inputCustomizer->chunk, $chunks))
            {
                continue;
            }

            $form->input(
                $inputCustomizer->name,
                function (Input $input) use ($form, $inputCustomizer)
                {
                    $inputCustomizer->init($form, $input);
                }
            );
        }
    }

}