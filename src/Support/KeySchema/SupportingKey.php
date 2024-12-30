<?php

namespace Mmb\Support\KeySchema;

trait SupportingKey
{

    protected string $type = 'text';
    protected $typeOptions = null;

    /**
     * Set key type to contact type
     *
     * @return $this
     */
    public function requestContact()
    {
        $this->type = 'contact';
        return $this;
    }

    /**
     * Set key type to location type
     *
     * @return $this
     */
    public function requestLocation()
    {
        $this->type = 'location';
        return $this;
    }

    /**
     * Set key type to request user type
     *
     * @param int $id
     * @param ...$namedArgs
     * @return $this
     */
    public function requestUser(int $id, ...$namedArgs)
    {
        $this->type = 'user';
        $this->typeOptions = $namedArgs + ['id' => $id];
        return $this;
    }

    /**
     * Set key type to request users type
     *
     * @param int $id
     * @param int $max
     * @param     ...$namedArgs
     * @return $this
     */
    public function requestUsers(int $id, int $max = 10, ...$namedArgs)
    {
        $this->type = 'users';
        $this->typeOptions = $namedArgs + ['id' => $id, 'max' => $max];
        return $this;
    }

    /**
     * Set key type to request chat type
     *
     * @param int $id
     * @param ...$namedArgs
     * @return $this
     */
    public function requestChat(int $id, ...$namedArgs)
    {
        $this->type = 'chat';
        $this->typeOptions = $namedArgs + ['id' => $id];
        return $this;
    }

    /**
     * Set key type to request poll
     *
     * @param ...$namedArgs
     * @return $this
     */
    public function requestPoll(...$namedArgs)
    {
        $this->type = 'poll';
        $this->typeOptions = $namedArgs;
        return $this;
    }

    public function getUniqueData(KeyboardInterface $base): ?string
    {
        return match ($this->type) {
            'text'     => KeyUniqueData::makeText($this->text),
            'contact'  => KeyUniqueData::makeContact(),
            'location' => KeyUniqueData::makeLocation(),
            'user'     => KeyUniqueData::makeRequestUser($this->typeOptions['id']),
            'users'    => KeyUniqueData::makeRequestUsers($this->typeOptions['id']),
            'chat'     => KeyUniqueData::makeRequestChat($this->typeOptions['id']),
            'poll'     => KeyUniqueData::makePoll(),
            default    => null,
        };
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function toArray(): array
    {
        return match ($this->type) {
            'contact'  => [
                'text' => $this->getText(),
                'requestContact' => true,
            ],
            'location' => [
                'text' => $this->getText(),
                'requestLocation' => true,
            ],
            'user'     => [
                'text' => $this->getText(),
                'requestUser' => $this->typeOptions,
            ],
            'users'    => [
                'text' => $this->getText(),
                'requestUsers' => $this->typeOptions,
            ],
            'chat'     => [
                'text' => $this->getText(),
                'requestChat' => $this->typeOptions,
            ],
            'poll'     => [
                'text' => $this->getText(),
                'requestPoll' => $this->typeOptions,
            ],
            default    => [
                'text' => $this->getText(),
            ],
        };
    }

}