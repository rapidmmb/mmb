<?php

namespace Mmb\Support\Encoding\Modes;

class None extends Mode
{

    public function text(StringContent|string $text) : StringContent
    {
        if (is_string($text))
        {
            return new StringContent($text);
        }

        return $text;
    }

    public function bold(StringContent|string $text) : StringContent
    {
        return $this->text($text);
    }

    public function italic(StringContent|string $text) : StringContent
    {
        return $this->text($text);
    }

    public function underline(StringContent|string $text) : StringContent
    {
        return $this->text($text);
    }

    public function strike(StringContent|string $text) : StringContent
    {
        return $this->text($text);
    }

    public function spoiler(StringContent|string $text) : StringContent
    {
        return $this->text($text);
    }

    public function url(StringContent|string $text, string $url) : StringContent
    {
        return $this->text($text);
    }

    public function emoji(StringContent|string $text, string $id) : StringContent
    {
        return $this->text($text);
    }

    public function code(StringContent|string $text) : StringContent
    {
        return $this->text($text);
    }

    public function pre(StringContent|string $text, ?string $language = null) : StringContent
    {
        return $this->text($text);
    }

    public function quotation(StringContent|string $text, bool $expandable = false) : StringContent
    {
        return $this->text($text);
    }
}
