<?php

namespace Mmb\Support\Telegram;

class KeysFactory
{

    /**
     * Make inline url key
     *
     * @param string $text
     * @param string $url
     * @return array
     */
    public function url(string $text, string $url)
    {
        return [
            'text' => $text,
            'url' => $url,
        ];
    }

    /**
     * Make inline normal key
     *
     * @param string $text
     * @param string $data
     * @return array
     */
    public function inline(string $text, string $data)
    {
        return [
            'text' => $text,
            'data' => $data,
        ];
    }

}
