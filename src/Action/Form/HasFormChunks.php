<?php

namespace Mmb\Action\Form;

use Mmb\Core\Updates\Update;

trait HasFormChunks
{

    protected $_chunkedCache = null;

    /**
     * @return array
     */
    public function inputs()
    {
        if(isset($this->_chunkedCache))
        {
            return $this->_chunkedCache;
        }

        $inputs = parent::inputs();

        if($chunk = $this->get('#'))
        {
            $new = [];
            foreach(is_array($chunk) ? $chunk : [$chunk] as $name)
            {
                if(array_key_exists($name, $inputs))
                {
                    $new[] = $inputs[$name];
                }
                elseif(in_array($name, $inputs))
                {
                    $new[] = $name;
                }
            }

            $inputs = $new;
        }

        $this->_chunkedCache = [];
        foreach($inputs as $input)
        {
            if(is_array($input))
            {
                array_push($this->_chunkedCache, ...$input);
            }
            else
            {
                $this->_chunkedCache[] = $input;
            }
        }

        return $this->_chunkedCache;
    }

    /**
     * @return bool
     */
    public function isChunkRequest()
    {
        return (bool) $this->get('#');
    }

    /**
     * Check current chunk
     *
     * @param string|array $name
     * @return bool
     */
    public function chunkIs(string|array $name)
    {
        $chunk = $this->get('#');
        if(is_array($name))
        {
            if(is_array($chunk))
            {
                foreach($name as $n)
                {
                    if(!in_array($chunk, $n))
                    {
                        return false;
                    }
                }

                return true;
            }
            else
            {
                return count($name) == 1 && $name[0] == $chunk;
            }
        }
        else
        {
            return is_array($chunk) ? in_array($name, $chunk) : $name == $chunk;
        }
    }

    /**
     * Check current chunk inputs
     *
     * @param string $input
     * @return bool
     */
    public function chunkHas(string $input)
    {
        return in_array($input, $this->inputs());
    }

    /**
     * Request form chunk
     *
     * @param string|array $chunk
     * @param array        $attributes
     * @param Update|null  $update
     * @return void
     */
    public static function requestChunk(string|array $chunk, array $attributes = [], Update $update = null)
    {
        $attributes['#'] = $chunk;
        static::request($attributes, $update);
    }

}
