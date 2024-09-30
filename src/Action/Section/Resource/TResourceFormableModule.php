<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;

trait TResourceFormableModule
{

    protected $inputs = [];
    protected $inputAttributes = [];

    /**
     * Add new input
     *
     * @return $this
     */
    public function input(string $name, Closure $callback, bool $include = true, string $as = null)
    {
        $this->inputs[$name] = $callback;

        if($include)
        {
            $this->inputAttributes[$as ?? $name] = $name;
        }

        return $this;
    }

    protected $attributes = [];

    /**
     * Define new custom attribute
     *
     * @return $this
     */
    public function attribute(string $name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Define custom attributes
     *
     * @return $this
     */
    public function attributes(array $values)
    {
        $this->attributes = array_replace($this->attributes, $values);
        return $this;
    }


    /**
     * Add text input
     *
     * @return $this
     */
    public function text(string $name, $message, $min = null, $max = null, Closure $init = null, bool $include = true)
    {
        return $this->input($name, function(Input $input) use($name, $message, $min, $max, $init)
        {
            $input->text()->prompt(fn() => $this->inputMessageOf($name, $message));
            if(isset($min)) $input->minLength($min);
            if(isset($max)) $input->maxLength($max);
            if(isset($init)) $this->valueOf($init, $input);
        }, $include);
    }

    /**
     * Add single line text input
     *
     * @return $this
     */
    public function textSingleLine(string $name, $message, $min = null, $max = null, Closure $init = null, bool $include = true)
    {
        return $this->input($name, function(Input $input) use($name, $message, $min, $max, $init)
        {
            $input->textSingleLine()->prompt(fn() => $this->inputMessageOf($name, $message));
            if(isset($min)) $input->minLength($min);
            if(isset($max)) $input->maxLength($max);
            if(isset($init)) $this->valueOf($init, $input);
        }, $include);
    }

    /**
     * Add integer input
     *
     * @return $this
     */
    public function int(string $name, $message, $min = null, $max = null, Closure $init = null, bool $include = true)
    {
        return $this->input($name, function(Input $input) use($name, $message, $min, $max, $init)
        {
            $input->int()->prompt(fn() => $this->inputMessageOf($name, $message));
            if(isset($min)) $input->min($min);
            if(isset($max)) $input->max($max);
            if(isset($init)) $this->valueOf($init, $input);
        }, $include);
    }

    /**
     * Add unsigned integer input
     *
     * @return $this
     */
    public function unsignedInt(string $name, $message, $min = null, $max = null, Closure $init = null, bool $include = true)
    {
        return $this->input($name, function(Input $input) use($name, $message, $min, $max, $init)
        {
            $input->unsignedInt()->prompt(fn() => $this->inputMessageOf($name, $message));
            if(isset($min)) $input->min($min);
            if(isset($max)) $input->max($max);
            if(isset($init)) $this->valueOf($init, $input);
        }, $include);
    }

    /**
     * Add float input
     *
     * @return $this
     */
    public function float(string $name, $message, $min = null, $max = null, Closure $init = null, bool $include = true)
    {
        return $this->input($name, function(Input $input) use($name, $message, $min, $max, $init)
        {
            $input->float()->prompt(fn() => $this->inputMessageOf($name, $message));
            if(isset($min)) $input->min($min);
            if(isset($max)) $input->max($max);
            if(isset($init)) $this->valueOf($init, $input);
        }, $include);
    }

    /**
     * Add unsigned float input
     *
     * @return $this
     */
    public function unsignedFloat(string $name, $message, $min = null, $max = null, Closure $init = null, bool $include = true)
    {
        return $this->input($name, function(Input $input) use($name, $message, $min, $max, $init)
        {
            $input->unsignedFloat()->prompt(fn() => $this->inputMessageOf($name, $message));
            if(isset($min)) $input->min($min);
            if(isset($max)) $input->max($max);
            if(isset($init)) $this->valueOf($init, $input);
        }, $include);
    }

    public function inputMessageOf(string $name, $message, ...$args)
    {
        $value = $this->valueOf($message, ...$args, name: $name);
        foreach($this->messagesCallback as $callback)
        {
            $value = $this->valueOf($callback, $value, ...$args, name: $name);
        }

        return $value;
    }

    protected $messagesCallback = [];

    /**
     * Add event to change the messages
     *
     * @param Closure $callback
     * @return $this
     */
    public function messages(Closure $callback)
    {
        $this->messagesCallback[] = $callback;
        return $this;
    }

    protected $chunks;

    /**
     * Define chunks
     *
     * @param array $chunks
     * @return $this
     */
    public function chunks(array $chunks)
    {
        $this->chunks = $chunks;
        return $this;
    }

    public function getInputs(InlineForm $form)
    {
        $inputs = $this->inputs;

        if($chunk = $this->currentChunk)
        {
            $form->form->put('_#', $chunk);
        }
        else $chunk = $form->form->get('_#');

        if($chunk)
        {
            $form->form->put('_#', $chunk);
            if(!is_array($chunk)) $chunk = [$chunk];
            $chunks = $this->chunks ?? array_keys($this->inputs);

            $inps = [];
            foreach($chunk as $name)
            {
                if(isset($chunks[$name]))
                {
                    foreach($chunks[$name] as $name)
                    {
                        $inps[$name] = $inputs[$name];
                    }
                }
                else
                {
                    $inps[$name] = $inputs[$name];
                }
            }

            return $inps;
        }

        return $inputs;
    }

    protected $currentChunk;

    protected function initForm(InlineForm $form)
    {
        $inputs = $this->getInputs($form);

        foreach($inputs as $name => $input)
        {
            $form->input($name, $input);
        }
    }

    protected function getFormAttributes(InlineForm $form)
    {
        $existsInputs = array_keys($this->getInputs($form));
        $attributes = [];
        foreach($this->inputAttributes as $as => $name)
        {
            if(in_array($name, $existsInputs))
            {
                $attributes[$as] = $form->$name;
            }
        }
        foreach($this->attributes as $as => $value)
        {
            $attributes[$as] = $this->valueOf($value);
        }

        return $attributes;
    }

    public function setCurrentChunk($chunk)
    {
        $this->currentChunk = $chunk;
        return $this;
    }

}
