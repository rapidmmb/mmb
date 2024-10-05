<?php

namespace Mmb\Support\Encoding\Modes;

use Closure;

abstract class Mode
{

    public abstract function text(string|StringContent $text) : StringContent;

    public abstract function bold(string|StringContent $text) : StringContent;

    public abstract function italic(string|StringContent $text) : StringContent;

    public abstract function underline(string|StringContent $text) : StringContent;

    public abstract function strike(string|StringContent $text) : StringContent;

    public abstract function spoiler(string|StringContent $text) : StringContent;

    public abstract function url(string|StringContent $text, string $url) : StringContent;

    public abstract function emoji(string|StringContent $text, string $id) : StringContent;

    public abstract function code(string|StringContent $text) : StringContent;

    public abstract function pre(string|StringContent $text, ?string $language = null) : StringContent;

    public abstract function quotation(string|StringContent $text, bool $expandable = false) : StringContent;


    public function string(string|StringContent $text) : StringContent
    {
        if (is_string($text))
        {
            return $this->text($text);
        }

        return $text;
    }

    public function user(string|StringContent $text, int $id) : StringContent
    {
        return $this->url($text, "tg://user?id=$id");
    }

    public function build(string|StringContent|Closure $text, string|StringContent ...$text2) : StringContent
    {
        if ($text instanceof Closure)
        {
            return $this->build(...$text($this));
        }

        array_unshift($text2, $text);
        return new StringContent(
            collect($text2)->map(fn ($txt) => $this->string($txt)->toString())->implode(''),
        );
    }

    public function trust(string|StringContent $text) : StringContent
    {
        return is_string($text) ? new StringContent($text) : $text;
    }

}
