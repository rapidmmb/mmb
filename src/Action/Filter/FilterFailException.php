<?php

namespace Mmb\Action\Filter;

use Exception;

class FilterFailException extends Exception
{

    public function __construct(
        public string $description,
        $exceptionMessage,
        public ?FilterFailException $next = null,
    )
    {
        parent::__construct($exceptionMessage);
    }

    /**
     * Implode errors
     *
     * @param string $separate
     * @return string
     */
    public function implode(string $separate)
    {
        $descriptions = [];
        $cur = $this;
        do $descriptions[] = $cur->description;
        while($cur = $cur->next);

        return implode($separate, $descriptions);
    }

    /**
     * Implode errors each lines
     *
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function lines(string $prefix = '', string $suffix = '')
    {
        return $prefix . $this->implode($suffix . "\n" . $prefix) . $suffix;
    }

}
