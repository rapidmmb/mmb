<?php

namespace Mmb\Action\Section;

use Mmb\Support\KeySchema\KeyboardInterface;
use Mmb\Support\KeySchema\KeyUniqueData;

class DialogKey extends MenuKey
{

    protected ?string $id = null;

    /**
     * Set unique id
     *
     * @param string $id
     * @return $this
     */
    public function id(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get key unique id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    protected function getActionId(): string
    {
        return $this->getId() ?? $this->getText();
    }

    public function getUniqueData(KeyboardInterface $base): ?string
    {
        return KeyUniqueData::makeDialog($this->getActionId());
    }

    public function toArray(): array
    {
        /** @var Dialog $dialog */
        $dialog = $this->menu;

        try {
            $data = $dialog->isFixed() ?
                $dialog->getFixedValue()->getMatcher(...$this->menu->getInitializer())->makeQuery(...$this->menu->getWithinData(), _action: $this->getActionId()) :
                GlobalDialogHandler::makeQuery($dialog->getUse(), $dialog->getUsed()->getKey(), $this->getActionId());
        } catch (\InvalidArgumentException $e) {
            [$class, $method] = $dialog->getInitializer();
            throw new \InvalidArgumentException("Failed to create dialog key [$this->text] in [$class::$method()], maybe some arguments are missing for the query", previous: $e);
        }

        return [
            'text' => $this->getText(),
            'data' => $data,
        ];
    }

}
