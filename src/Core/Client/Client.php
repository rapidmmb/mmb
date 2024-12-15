<?php

namespace Mmb\Core\Client;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Mmb\Core\Bot;
use Mmb\Core\Client\Parser\ArgsParser;
use Mmb\Core\Client\Query\UploadContents;
use Mmb\Core\Client\Query\UploadFile;

abstract class Client
{

    public bool $ignore = false;

    public function __construct(
        public Bot       $bot,
        protected string $token,
        public string    $method,
        public array     $args,
    )
    {
    }

    protected abstract function execute();

    public final function request()
    {
        try {

            return $this->execute();

        } catch (\Throwable $throwable) { // todo filter server errors only
            if ($this->ignore) {
                return false;
            }

            throw $throwable;
        }
    }


    private array $_parsedArgs;

    /**
     * Get parsed args
     *
     * @return array
     */
    public function parsedArgs(): array
    {
        return $this->_parsedArgs ??= ArgsParser::normalize($this);
    }

    private array $_jsonListArgs;

    /**
     * Get list of args with json encoding in required parameters
     *
     * @return array
     */
    public function getJsonListArgs(): array
    {
        if (!isset($this->_jsonListArgs)) {
            $this->_jsonListArgs = $this->parsedArgs();
            foreach ($this->_jsonListArgs as $name => $value) {
                if (is_array($value)) {
                    $this->_jsonListArgs[$name] = json_encode($value);
                } elseif ($value instanceof Jsonable) {
                    $this->_jsonListArgs[$name] = $value->toJson();
                }
            }
        }

        return $this->_jsonListArgs;
    }


    private string $_lowerMethod;

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
     * @param bool $isLower
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
//    protected function wrapQuery(array $query)
//    {
//        // Normal query type
//        if (!Arr::first($query, fn($item) => is_resource($item))) {
//            return ['query' => $query];
//        }
//
//        // Multipart query type
//        $multipart = [];
//        foreach ($query as $key => $value) {
//            $multipart[] = [
//                'name' => $key,
//                'contents' => $value,
//            ];
//        }
//
//        return ['multipart' => $multipart];
//    }

    /**
     * Checks the query has upload file
     *
     * @param array $query
     * @return array
     */
    protected function hasUpload(array $query)
    {
        return Arr::first($query,
            fn($item) => is_resource($item) ||
                $item instanceof UploadFile ||
                $item instanceof UploadContents ||
                $item instanceof \CURLFile
        );
    }

}
