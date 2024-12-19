<?php

namespace Mmb\KeySchema;

use Closure;
use Mmb\Core\Updates\Update;

interface KeyboardInterface
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

    public function detectUniqueData(Update $update): ?string;

    public function makeKey(string $text, Closure $callback, array $args): KeyInterface;

}