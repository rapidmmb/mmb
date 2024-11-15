<?php

namespace Mmb\Core\Requests;

use Illuminate\Support\Arr;
use Mmb\Core\Bot;
use Mmb\Core\Requests\Parser\ArgsParser;

abstract class RequestApi
{

    public bool $ignore = false;

    public function __construct(
        public Bot $bot,
        protected string $token,
        public string $method,
        public array $args,
    )
    {
    }

    protected abstract function execute();

    public final function request()
    {
        try
        {
            return $this->execute();
        }
        catch(\Throwable $throwable)
        {
            if($this->ignore)
            {
                return false;
            }

            throw $throwable;
        }
    }


    private $_parsedArgs;

    /**
     * Get parsed args
     *
     * @return array
     */
    public function parsedArgs()
    {
        return $this->_parsedArgs ??= ArgsParser::normalize($this);
    }


    private $_lowerMethod;

    /**
     * Get lower case method
     *
     * @return string
     */
    public function lowerMethod()
    {
        return $this->_lowerMethod ??= strtolower($this->method);
    }

    /**
     * Check method ignore case
     *
     * @param string $name
     * @param bool   $isLower
     * @return bool
     */
    public function isMethod(string $name, bool $isLower = false)
    {
        return $this->lowerMethod() == ($isLower ? $name : strtolower($name));
    }

    /**
     * Change the method name
     *
     * @param string $method
     * @return $this
     */
    public function changeMethod(string $method)
    {
        $this->method = $method;
        unset($this->_lowerMethod);
        unset($this->_isSending);
        unset($this->_isEditing);
        return $this;
    }


    private $_isSending;

    /**
     * Check method is sending something method
     *
     * @return bool
     */
    public function isSendMethod()
    {
        return $this->_isSending ??= str_starts_with($this->lowerMethod(), 'send');
    }

    private $_isEditing;

    /**
     * Check method is editing something method
     *
     * @return bool
     */
    public function isEditMethod()
    {
        return $this->_isEditing ??= str_starts_with($this->lowerMethod(), 'edit');
    }

    /**
     * Wrap query
     *
     * @param array $query
     * @return array
     */
    protected function wrapQuery(array $query)
    {
        // Normal query type
        if (! Arr::first($query, fn ($item) => is_resource($item)))
        {
            return ['query' => $query];
        }

        // Multipart query type
        $multipart = [];
        foreach ($query as $key => $value)
        {
            $multipart[] = [
                'name'     => $key,
                'contents' => $value,
            ];
        }

        return ['multipart' => $multipart];
    }

}
