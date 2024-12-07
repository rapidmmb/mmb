<?php

namespace Mmb\Context\Contracts;

use Mmb\Context;

interface ContextAttribute
{

    public function get(Context $context, string $key, mixed $default): mixed;

    public function put(Context $context, string $key, mixed $value): void;

    public function has(Context $context, string $key): bool;

    public function forget(Context $context, string $key): void;

}