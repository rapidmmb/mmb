<?php

namespace Mmb\Support\KeySchema;

use Closure;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;

interface KeyboardInterface
{

    public function makeKey(string $text, Closure $callback, array $args): KeyInterface;

    public function restoreActionCallback(array $value): ?ActionCallback;

}