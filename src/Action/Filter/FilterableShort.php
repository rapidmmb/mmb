<?php

namespace Mmb\Action\Filter;

trait FilterableShort
{

    /**
     * Add message filter
     *
     * @param $messageError
     * @return $this
     */
    public function message($messageError = null)
    {
        $this->getFilter()->message(...func_get_args());
        return $this;
    }

    /**
     * Add text message filter
     *
     * @param $textError
     * @param $messageError
     * @return $this
     */
    public function text($textError = null, $messageError = null)
    {
        $this->getFilter()->text(...func_get_args());
        return $this;
    }

    /**
     * Add single line text message filter
     *
     * @param $singleLineError
     * @param $textError
     * @param $messageError
     * @return $this
     */
    public function textSingleLine($singleLineError = null, $textError = null, $messageError = null)
    {
        $this->getFilter()->textSingleLine(...func_get_args());
        return $this;
    }

    /**
     * Add float number message filter
     *
     * @param $numberError
     * @param $messageError
     * @param bool $unsigned
     * @return $this
     */
    public function float($numberError = null, $messageError = null, bool $unsigned = false)
    {
        $this->getFilter()->float(...func_get_args());
        return $this;
    }

    /**
     * Add float number message filter
     *
     * @param $numberError
     * @param $messageError
     * @return $this
     */
    public function unsignedFloat($numberError = null, $messageError = null)
    {
        $this->getFilter()->unsignedFloat(...func_get_args());
        return $this;
    }

    /**
     * Add integer number message filter
     *
     * @param $numberError
     * @param $messageError
     * @param bool $unsigned
     * @return $this
     */
    public function int($numberError = null, $messageError = null, bool $unsigned = false)
    {
        $this->getFilter()->int(...func_get_args());
        return $this;
    }

    /**
     * Add integer number message filter
     *
     * @param $numberError
     * @param $messageError
     * @return $this
     */
    public function unsignedInt($numberError = null, $messageError = null)
    {
        $this->getFilter()->unsignedInt(...func_get_args());
        return $this;
    }

    /**
     * Clamp number between two value
     *
     * @param      $min
     * @param      $max
     * @param      $minError
     * @param      $maxError
     * @param      $error
     * @return $this
     */
    public function clamp(
        $min = null, $max = null, $minError = null, $maxError = null, $error = null
    )
    {
        $this->getFilter()->clamp(...func_get_args());
        return $this;
    }

    /**
     * Filter minimum number
     *
     * @param $min
     * @param $error
     * @return $this
     */
    public function min($min, $error = null)
    {
        $this->getFilter()->min(...func_get_args());
        return $this;
    }

    /**
     * Filter maximum number
     *
     * @param $max
     * @param $error
     * @return $this
     */
    public function max($max, $error = null)
    {
        $this->getFilter()->max(...func_get_args());
        return $this;
    }

    /**
     * Filter string length
     *
     * @param      $min
     * @param      $max
     * @param      $minError
     * @param      $maxError
     * @param      $error
     * @param bool $ascii
     * @return $this
     */
    public function length(
        $min = null, $max = null, $minError = null, $maxError = null, $error = null, bool $ascii = true
    )
    {
        $this->getFilter()->length(...func_get_args());
        return $this;
    }

    /**
     * Filter minimum string length
     *
     * @param      $min
     * @param      $error
     * @param bool $ascii
     * @return $this
     */
    public function minLength($min, $error = null, bool $ascii = true)
    {
        $this->getFilter()->minLength(...func_get_args());
        return $this;
    }

    /**
     * Filter maximum string length
     *
     * @param      $max
     * @param      $error
     * @param bool $ascii
     * @return $this
     */
    public function maxLength($max, $error = null, bool $ascii = true)
    {
        $this->getFilter()->maxLength(...func_get_args());
        return $this;
    }

    /**
     * Filter regex pattern
     *
     * @param string $pattern
     * @param int    $result
     * @param        $error
     * @return $this
     */
    public function regex(string $pattern, int $result = -1, $error = null)
    {
        $this->getFilter()->regex(...func_get_args());
        return $this;
    }

}
