<?php

namespace Mmb\Support\Encoding;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Mmb\Support\Encoding\Modes\StringContent;
use Mmb\Support\Encoding\Modes\Mode;
use Stringable;

class TextBuilder implements Stringable
{
    use Conditionable, Macroable;

    public readonly Mode $mode;

    protected array $lines = [];

    public function __construct(
        string|Mode                         $mode = 'none',
        protected string|StringContent|null $prefix = null,
        protected string|StringContent|null $suffix = null,
        protected string|StringContent|null $separate = null,
        protected string|StringContent|null $division = null,
        protected string|StringContent|null $subtitle = null,
    )
    {
        if (is_string($mode))
        {
            $mode = Text::mode($mode);
        }

        $this->mode = $mode;
    }

    public static function make(
        string|Mode               $mode = 'none',
        string|StringContent|null $prefix = null,
        string|StringContent|null $suffix = null,
        string|StringContent|null $separate = null,
        string|StringContent|null $division = null,
        string|StringContent|null $subtitle = null,
    )
    {
        return new static(...func_get_args());
    }

    /**
     * Set prefix value
     *
     * @param string|StringContent|null $value
     * @return $this
     */
    public function prefix(string|StringContent|null $value)
    {
        $this->prefix = $value;
        return $this;
    }

    /**
     * Set suffix value
     *
     * @param string|StringContent|null $value
     * @return $this
     */
    public function suffix(string|StringContent|null $value)
    {
        $this->suffix = $value;
        return $this;
    }

    /**
     * Set separate value
     *
     * @param string|StringContent|null $value
     * @return $this
     */
    public function separate(string|StringContent|null $value)
    {
        $this->separate = $value;
        return $this;
    }

    /**
     * Set division value
     *
     * @param string|StringContent|null $value
     * @return $this
     */
    public function division(string|StringContent|null $value)
    {
        $this->division = $value;
        return $this;
    }

    /**
     * Set subtitle value
     *
     * @param string|StringContent|null $value
     * @return $this
     */
    public function subtitle(string|StringContent|null $value)
    {
        $this->subtitle = $value;
        return $this;
    }

    protected array $valueUsing = ['string'];

    /**
     * Add value style (from mode encoding)
     *
     * @param string $name
     * @return $this
     */
    public function valueStyle(string $name)
    {
        $this->valueUsing[] = $name;
        return $this;
    }


    /**
     * Get value using
     *
     * @param string|StringContent $content
     * @param array                $using
     * @param bool                 $forceEncode
     * @return StringContent|string
     */
    protected function getUsing(string|StringContent $content, array $using, bool $forceEncode = false)
    {
        foreach ($using as $use)
        {
            $content = $this->mode->$use($content);
        }

        if ($forceEncode && is_string($content))
        {
            $content = $this->mode->string($content);
        }

        return $content;
    }


    /**
     * Build the text using callback
     *
     * @param Closure(TextBuilder $text, Mode $mode) : mixed $callback
     * @return string
     */
    public function build(Closure $callback) : string
    {
        $callback($this, $this->mode);
        return $this->toString();
    }

    /**
     * Add blank line
     *
     * @param string|StringContent $text
     * @return $this
     */
    public function blank(string|StringContent $text)
    {
        $this->lines[] = $this->mode->string($text)->toString();
        return $this;
    }

    /**
     * Add trusted line (the text will not encode)
     *
     * @param string $text
     * @return $this
     */
    public function trusted(string $text)
    {
        $this->lines[] = $text;
        return $this;
    }

    /**
     * Add a line
     *
     * @param string|StringContent      $text
     * @param string|StringContent|null $value
     * @param string|null               $prefix
     * @param string|null               $suffix
     * @param string|null               $separate
     * @return $this
     */
    public function line(
        string|StringContent $text,
        string|StringContent|null $value = null,
        ?string $prefix = null,
        ?string $suffix = null,
        ?string $separate = null,
    )
    {
        $prefix ??= $this->prefix;
        $suffix ??= $this->suffix;

        if (isset($value))
        {
            $separate ??= $this->separate;
            $text = $this->mode->string($text) . $this->mode->string($separate ?? '') . $this->getUsing($value, $this->valueUsing, true);
        }
        else
        {
            $text = $this->mode->string($text);
        }

        $this->lines[] = $this->mode->string($prefix ?? '') . $text . $this->mode->string($suffix ?? '');
        return $this;
    }

    /**
     * Add division to the text
     *
     * @param string|null $division
     * @return $this
     */
    public function div(?string $division = null)
    {
        return $this->blank($division ?? $this->division ?? '');
    }


    /**
     * Add a space (2 line space)
     *
     * @return $this
     */
    public function space()
    {
        return $this->space2();
    }

    /**
     * Add 1 line space
     *
     * @return $this
     */
    public function space1()
    {
        return $this->blank('');
    }

    /**
     * Add 2 line space
     *
     * @return $this
     */
    public function space2()
    {
        return $this->blank("\n");
    }

    /**
     * Add 3 line space
     *
     * @return $this
     */
    public function space3()
    {
        return $this->blank("\n\n");
    }

    /**
     * Add 4 line space
     *
     * @return $this
     */
    public function space4()
    {
        return $this->blank("\n\n\n");
    }

    /**
     * Add 6 line space
     *
     * @return $this
     */
    public function space6()
    {
        return $this->blank("\n\n\n\n\n");
    }


    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return implode("\n", $this->lines) . (isset($this->subtitle) ? "\n" . $this->subtitle : '');
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->__toString();
    }

}
