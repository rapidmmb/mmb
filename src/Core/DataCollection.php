<?php

namespace Mmb\Core;

use ArrayObject;
use Traversable;

/**
 * @template T
 */
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

    /**
     * @return T
     */
    public function getDefault()
    {
        return $this->first();
    }

    /**
     * @param int $index
     * @return T
     */
    public function get(int $index)
    {
        return $this->allData[$index];
    }

    /**
     * @return T
     */
    public function first()
    {
        return $this->allData[0];
    }

    /**
     * @return T
     */
    public function last()
    {
        return last($this->allData);
    }

    public function count()
    {
        return count($this->allData);
    }

    /**
     * @return Traversable<int, T>
     */
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
