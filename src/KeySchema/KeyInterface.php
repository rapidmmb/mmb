<?php

namespace Mmb\KeySchema;

interface KeyInterface
{

    public function getUniqueData(KeyboardInterface $base): ?string;

    public function click(KeyboardInterface $base): void;

    public function isVisible(): bool;

    public function isDisplayed(): bool;

    public function isIncluded(): bool;

    public function toArray(): array;

    public function isStorable(): bool;

    public function toStorable(): mixed;

}