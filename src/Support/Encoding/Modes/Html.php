<?php

namespace Mmb\Support\Encoding\Modes;

class Html extends Mode
{

    public function text(string|StringContent $text) : StringContent
    {
        return new StringContent(
            str_replace(
                ['&', '<', '>',],
                ["&amp;", "&lt;", "&gt;"],
                (string) $text
            )
        );
    }

    public function bold(string|StringContent $text) : StringContent
    {
        return new StringContent("<b>" . $this->string($text) . "</b>");
    }

    public function italic(string|StringContent $text) : StringContent
    {
        return new StringContent("<i>" . $this->string($text) . "</i>");
    }

    public function underline(string|StringContent $text) : StringContent
    {
        return new StringContent("<u>" . $this->string($text) . "</u>");
    }

    public function strike(string|StringContent $text) : StringContent
    {
        return new StringContent("<s>" . $this->string($text) . "</s>");
    }

    public function spoiler(string|StringContent $text) : StringContent
    {
        return new StringContent("<tg-spoiler>" . $this->string($text) . "</tg-spoiler>");
    }

    public function url(string|StringContent $text, string $url) : StringContent
    {
        return new StringContent("<a href='" . $this->string($url) . "'>" . $this->string($text) . "</a>");
    }

    public function emoji(string|StringContent $text, string $id) : StringContent
    {
        return new StringContent("<tg-emoji emoji-id='$id'>" . $this->string($text) . "</tg-emoji>");
    }

    public function code(string|StringContent $text) : StringContent
    {
        return new StringContent("<code>" . $this->string($text) . "</code>");
    }

    public function pre(string|StringContent $text, ?string $language = null) : StringContent
    {
        if (is_null($language))
        {
            return new StringContent("<pre>" . $this->string($text) . "</pre>");
        }
        else
        {
            return new StringContent("<pre><code class='language-$language'>" . $this->string($text) . "</code></pre>");
        }
    }

    public function quotation(string|StringContent $text, bool $expandable = false) : StringContent
    {
        return new StringContent(
            "<blockquote" . ($expandable ? ' expandable' : '') . ">" . $this->string($text) . "</blockquote>"
        );
    }

}
