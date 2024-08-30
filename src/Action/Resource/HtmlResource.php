<?php

namespace Mmb\Action\Resource;

/**
 * @deprecated
 */
class HtmlResource
{

    public function prefix()
    {
    }

    public function suffix()
    {
    }

    /**
     * Get content
     *
     * @param string $name
     * @param        ...$args
     * @return array|string
     */
    public static function get(string $name, ...$args)
    {
        ob_start();

        $instance = app(static::class);
        
        $instance->prefix();
        $data = $instance->$name(...$args);
        $instance->suffix();

        $content = ob_get_clean();
        if($content === false) $content = '';

        if(is_array($data))
        {
            $data['text'] = $content;
        }
        else
        {
            $data = $content;
        }

        return $data;
    }

    /**
     * Get content as string
     *
     * @param string $name
     * @param        ...$args
     * @return string
     */
    public static function getString(string $name, ...$args)
    {
        $data = static::get($name, ...$args);

        if(is_array($data))
        {
            return $data['text'];
        }

        return $data;
    }

    /**
     * Get content as an array
     *
     * @param string $name
     * @param        ...$args
     * @return array
     */
    public static function getArray(string $name, ...$args)
    {
        $data = static::get($name, ...$args);

        if(!is_array($data))
        {
            return ['text' => $data];
        }

        return $data;
    }

}
