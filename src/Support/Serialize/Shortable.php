<?php

namespace Mmb\Support\Serialize;

interface Shortable
{

    public function shortSerialize() : array;

    public function shortUnserialize(array $data) : void;

}
