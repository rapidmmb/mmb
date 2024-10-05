<?php

namespace Mmb\Support\Format;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Conditionable;
use IteratorAggregate;
use Traversable;

class KeyFormatterBuilder implements IteratorAggregate, Countable, Arrayable
{
    use Conditionable;

    public function __construct(
        protected array $key,
    )
    {
    }

    public function getIterator() : Traversable
    {
        return new \ArrayIterator($this->key);
    }

    public function count() : int
    {
        return count($this->key);
    }

    public function toArray()
    {
        return $this->key;
    }


    public function rtl()
    {
        return new static(
            KeyFormatter::rtl($this->key),
        );
    }

    public function maxColumns(int $max, bool $wrap = true)
    {
        return new static(
            KeyFormatter::maxColumns($this->key, $max, $wrap),
        );
    }

    public function wrap(int $max)
    {
        return new static(
            KeyFormatter::wrap($this->key, $max),
        );
    }

    public function wrapHidden(int $max)
    {
        return new static(
            KeyFormatter::wrapHidden($this->key, $max),
        );
    }

    public function resize(int $columns)
    {
        return new static(
            KeyFormatter::resize($this->key, $columns),
        );
    }

    public function resizeAuto(int $size = 40)
    {
        return new static(
            KeyFormatter::resizeAuto($this->key, $size),
        );
    }

}
