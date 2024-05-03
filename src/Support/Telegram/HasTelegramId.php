<?php

namespace Mmb\Support\Telegram;

trait HasTelegramId
{

    public function initializeHasTelegramId()
    {
        $this->mergeFillable([$this->getTelegramIdColumn()]);
    }

    // protected $telegramIdColumn = 'telegram_id';

    /**
     * Get telegram id column
     *
     * @return string
     */
    public function getTelegramIdColumn()
    {
        return isset($this->telegramIdColumn) ? $this->telegramIdColumn : 'telegram_id';
    }

    /**
     * Get telegram id
     *
     * @return int
     */
    public function getTelegramId()
    {
        return $this->{$this->getTelegramIdColumn()};
    }

    /**
     * Set telegram id
     *
     * @param $id
     * @return void
     */
    public function setTelegramId($id)
    {
        $this->{$this->getTelegramIdColumn()} = $id;
    }

}
