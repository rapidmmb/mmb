<?php

namespace Mmb\Support\KeySchema;

use Illuminate\Contracts\Support\Arrayable;
use Mmb\Support\Action\ActionCallback;

interface KeyInterface extends Arrayable
{

    public function getUniqueData(KeyboardInterface $base): ?string;

    public function toAction(): ?ActionCallback;

    public function getText(): string;

    public function isVisible(): bool;

    public function isDisplayed(): bool;

    public function isIncluded(): bool;

    public function toArray(): array;

}