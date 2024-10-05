<?php

namespace Mmb\Support\Encoding\Modes;

class Markdown extends Mode
{

    public function text(string|StringContent $text) : StringContent
    {
        return new StringContent(
            str_replace(
                ["\\", '_', '*', '`', '['],
                ["\\\\", "\\_", "\\*", "\\`", "\\["],
                (string) $text
            ),
        );
    }

    public function bold(string|StringContent $text) : StringContent
    {
        return new StringContent("*" . $this->string($text) . "*");
    }

    public function italic(string|StringContent $text) : StringContent
    {
        return new StringContent("_" . $this->string($text) . "_");
    }

    public function underline(string|StringContent $text) : StringContent
    {
        return $this->string($text); // Unsupported
    }

    public function strike(string|StringContent $text) : StringContent
    {
        return $this->string($text); // Unsupported
    }

    public function spoiler(string|StringContent $text) : StringContent
    {
        return $this->string($text); // Unsupported
    }

    public function url(string|StringContent $text, string $url) : StringContent
    {
        return new StringContent("[" . $this->string($text) . "]($url)");
    }

    public function emoji(string|StringContent $text, string $id) : StringContent
    {
        return $this->string($text); // Unsupported
    }

    public function code(string|StringContent $text) : StringContent
    {
        return new StringContent("`" . $this->string($text) . "`");
    }

    public function pre(string|StringContent $text, ?string $language = null) : StringContent
    {
        return new StringContent("```$language\n" . $this->string($text) . "\n```");
    }

    public function quotation(string|StringContent $text, bool $expandable = false) : StringContent
    {
        return $this->pre($text); // Unsupported
    }

}
