<?php

namespace Mmb\Action\Contracts;

use Closure;

interface Menuable
{

    /**
     * Add menu schema
     *
     * @param array $key
     * @return void
     */
    public function addMenuSchema(array $key) : void;

    /**
     * Create an action key
     *
     * @param string  $text
     * @param Closure $callback
     * @return mixed
     */
    public function createActionKey(string $text, Closure $callback);

}