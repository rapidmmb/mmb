<?php

namespace Mmb\Core\Updates\Data;

use Mmb\Core\Data;

/**
 * @property string $phoneNumber
 * @property string $firstName
 * @property string $lastName
 * @property int    $userId
 *
 * @property string $name
 */
class Contact extends Data
{

    protected function dataCasts() : array
    {
        return [
            'phone_number' => 'string',
            'first_name'   => 'string',
            'last_name'    => 'string',
            'user_id'      => 'int',
        ];
    }

    public function getNameAttribute()
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }


    public function send(array $args = [], ...$namedArgs)
    {
        $this->bot()->send($args + $namedArgs + [
            'type' => 'contact',
            'phone_number' => $this->phoneNumber,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
        ]);
    }

}
