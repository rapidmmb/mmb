<?php

namespace Mmb\Core;

use ArrayObject;
use Traversable;

trait DataCollection
{

    protected function initialize(array $data, bool $trustedData)
    {
        $castTo = $this->getCollectionClassType();
        foreach($data as $i => $item)
        {
            $data[$i] = $this->castSingleData($item, $castTo, $trustedData);
        }

        $this->allData = $data;
    }

    protected function dataCasts() : array
    {
        return [];
    }

    protected function dataRules() : array
    {
        return [];
    }

    protected abstract function getCollectionClassType();

    public function getDefault()
    {
        return $this->first();
    }

    public function get(int $index)
    {
        return $this->allData[$index];
    }

    public function first()
    {
        return $this->allData[0];
    }

    public function last()
    {
        return last($this->allData);
    }

    public function count()
    {
        return count($this->allData);
    }

    public function getIterator() : Traversable
    {
        return (new ArrayObject($this->allData))->getIterator();
    }

    public function __get(string $name)
    {
        return $this->getDefault()->$name;
    }

    public function __set(string $name, $value) : void
    {
        $this->getDefault()->$name = $value;
    }

}
