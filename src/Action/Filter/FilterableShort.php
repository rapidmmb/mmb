<?php

namespace Mmb\Action\Filter;

use Closure;

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
     * Add message type filter
     *
     * @param string|array $types
     * @param              $typeError
     * @param              $messageError
     * @return $this
     */
    public function messageType(string|array $types, $typeError, $messageError = null)
    {
        $this->getFilter()->messageType(...func_get_args());
        return $this;
    }

    /**
     * Add message global type filter
     *
     * @param string|array $types
     * @param              $typeError
     * @param              $messageError
     * @return $this
     */
    public function messageGlobalType(string|array $types, $typeError, $messageError = null)
    {
        $this->getFilter()->messageGlobalType(...func_get_args());
        return $this;
    }

    /**
     * Add message media filter
     *
     * @param $mediaError
     * @param $messageError
     * @return $this
     */
    public function media($mediaError = null, $messageError = null)
    {
        $this->getFilter()->media(...func_get_args());
        return $this;
    }

    /**
     * Add message media or text filter
     *
     * @param $mediaError
     * @param $messageError
     * @return $this
     */
    public function mediaOrText($mediaError = null, $messageError = null)
    {
        $this->getFilter()->mediaOrText(...func_get_args());
        return $this;
    }

    /**
     * Add message builder filter
     *
     * @param $mediaError
     * @param $messageError
     * @return $this
     */
    public function messageBuilder($mediaError = null, $messageError = null)
    {
        $this->getFilter()->messageBuilder(...func_get_args());
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
     * @param mixed $numberError
     * @param mixed $textError
     * @param mixed $messageError
     * @param bool $unsigned
     * @return $this
     */
    public function float($numberError = null, $textError = null, $messageError = null, bool $unsigned = false)
    {
        $this->getFilter()->float(...func_get_args());
        return $this;
    }

    /**
     * Add float number message filter
     *
     * @param mixed $numberError
     * @param mixed $textError
     * @param mixed $messageError
     * @return $this
     */
    public function unsignedFloat($numberError = null, $textError = null, $messageError = null)
    {
        $this->getFilter()->unsignedFloat(...func_get_args());
        return $this;
    }

    /**
     * Add integer number message filter
     *
     * @param mixed $numberError
     * @param mixed $textError
     * @param mixed $messageError
     * @param bool  $unsigned
     * @return $this
     */
    public function int($numberError = null, $textError = null, $messageError = null, bool $unsigned = false)
    {
        $this->getFilter()->int(...func_get_args());
        return $this;
    }

    /**
     * Add integer number message filter
     *
     * @param mixed $numberError
     * @param mixed $textError
     * @param mixed $messageError
     * @return $this
     */
    public function unsignedInt($numberError = null, $textError = null, $messageError = null)
    {
        $this->getFilter()->unsignedInt(...func_get_args());
        return $this;
    }

    /**
     * Add number message filter
     *
     * @param mixed $numberError
     * @param mixed $textError
     * @param mixed $messageError
     * @param bool  $unsigned
     * @return $this
     */
    public function number($numberError = null, $textError = null, $messageError = null, bool $unsigned = false)
    {
        $this->getFilter()->int(...func_get_args());
        return $this;
    }

    /**
     * Add number message filter
     *
     * @param mixed $numberError
     * @param mixed $textError
     * @param mixed $messageError
     * @return $this
     */
    public function unsignedNumber($numberError = null, $textError = null, $messageError = null)
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
     * Filter divisible number
     *
     * @param $number
     * @param $error
     * @return $this
     */
    public function divisible($number, $error = null)
    {
        $this->getFilter()->divisible(...func_get_args());
        return $this;
    }

    /**
     * Filter regex pattern
     *
     * @param string     $pattern
     * @param int|string $result
     * @param mixed      $error
     * @return $this
     */
    public function regex(string $pattern, int|string $result = '', $error = null)
    {
        $this->getFilter()->regex(...func_get_args());
        return $this;
    }

    /**
     * Filter force forward
     *
     * @param bool $fromUser
     * @param bool $fromChannel
     * @param $message
     * @param $messageError
     * @return $this
     */
    public function forwarded(
        bool $fromUser = true,
        bool $fromChannel = true,
             $message = null,
             $messageError = null
    )
    {
        $this->getFilter()->forwarded(...func_get_args());
        return $this;
    }

    /**
     * Filter force forward
     *
     * @param $message
     * @param $messageError
     * @return $this
     */
    public function shouldForward($message = null, $messageError = null)
    {
        $this->getFilter()->shouldForward(...func_get_args());
        return $this;
    }

    /**
     * Filter force forward
     *
     * @param $message
     * @param $messageError
     * @return $this
     */
    public function shouldForwardFromUser($message = null, $messageError = null)
    {
        $this->getFilter()->shouldForwardFromUser(...func_get_args());
        return $this;
    }

    /**
     * Filter force forward
     *
     * @param $message
     * @param $messageError
     * @return $this
     */
    public function shouldForwardFromChannel($message = null, $messageError = null)
    {
        $this->getFilter()->shouldForwardFromChannel(...func_get_args());
        return $this;
    }

    /**
     * Filter force forward
     *
     * @param $message
     * @param $messageError
     * @return $this
     */
    public function notForwarded($message = null, $messageError = null)
    {
        $this->getFilter()->notForwarded(...func_get_args());
        return $this;
    }

    /**
     * Filter the item exists in table
     *
     * @param string       $table
     * @param string|null  $column
     * @param Closure|null $query
     * @param              $message
     * @return $this
     */
    public function exists(string $table, ?string $column = null, ?Closure $query = null, $message = null)
    {
        $this->getFilter()->exists(...func_get_args());
        return $this;
    }

    /**
     * Filter the item not exists in table
     *
     * @param string       $table
     * @param string|null  $column
     * @param mixed        $expect
     * @param Closure|null $query
     * @param null         $message
     * @return $this
     */
    public function unique(string $table, ?string $column = null, $expect = null, ?Closure $query = null, $message = null)
    {
        $this->getFilter()->unique(...func_get_args());
        return $this;
    }

}
