<?php

namespace Mmb\Support\Encoding\Modes;

class Markdown2Encoding extends TextModeEncoding
{

    public function text(string|StringContent $text) : StringContent
    {
        return new StringContent(
            preg_replace('/[\\\\_\*\[\]\(\)~`>\#\+\-=\|\{\}\.\!]/', '\\\\$0', (string) $text)
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
        return new StringContent("__" . $this->string($text) . "__");
    }

    public function strike(string|StringContent $text) : StringContent
    {
        return new StringContent("~" . $this->string($text) . "~");
    }

    public function spoiler(string|StringContent $text) : StringContent
    {
        return new StringContent("||" . $this->string($text) . "||");
    }

    public function url(string|StringContent $text, string $url) : StringContent
    {
        return new StringContent("[" . $this->string($text) . "]($url)");
    }

    public function emoji(string|StringContent $text, string $id) : StringContent
    {
        return new StringContent("![" . $this->string($text) . "](tg://emoji?id=$id)");
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
        $text = $this->string($text)->explode("\n")->map(fn ($line) => ">$line")->implode("\n");

        if ($expandable)
        {
            $text .= "||";
        }

        return new StringContent($text . "\n**");
    }

}
