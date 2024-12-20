<?php

namespace Mmb\Support\KeySchema;

use Closure;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;

interface KeyboardInterface
{

//    /**
//     * Add menu schema
//     *
//     * @param array $key
//     * @return void
//     */
//    public function addMenuSchema(array $key) : void;
//
//    /**
//     * Create an action key
//     *
//     * @param string  $text
//     * @param Closure $callback
//     * @return mixed
//     */
//    public function createActionKey(string $text, Closure $callback);

    public function makeKey(string $text, Closure $callback, array $args): KeyInterface;

    public function restoreActionCallback(array $value): ?ActionCallback;

}