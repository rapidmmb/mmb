<?php

namespace Mmb\Action\Section;

class DialogKey extends MenuKey
{

    protected $id;

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

    public function getId()
    {
        return $this->id;
    }


    public function getActionKey(bool $isInline = false)
    {
        return 'D' . ($this->getId() ?? $this->getText());
    }

    public function getAttributes()
    {
        try
        {
            $data = $this->menu->isFixed() ?
                $this->menu->getFixedValue()->getMatcher(...$this->menu->getInitializer())->makeQuery(...$this->menu->getWithinData(), _action: $this->getId() ?? $this->getText()) :
                GlobalDialogHandler::makeQuery($this->menu->getUse(), $this->menu->getUsed()->id, $this->getId() ?? $this->getText());
        }
        catch (\InvalidArgumentException $e)
        {
            [$class, $method] = $this->menu->getInitializer();
            throw new \InvalidArgumentException("Failed to create dialog key [$this->text] in [$class::$method()], maybe some arguments are missing for the query", previous: $e);
        }

        return [
            'text' => $this->getText(),
            'data' => $data,
        ];
    }

}
