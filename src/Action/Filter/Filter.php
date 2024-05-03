<?php

namespace Mmb\Action\Filter;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Traits\Macroable;
use Mmb\Core\Updates\Update;

class Filter extends FilterRule
{
    use Macroable;

    public function __construct()
    {
    }

    /**
     * Make new filter grouping
     *
     * @return static
     */
    public static function make()
    {
        return new static;
    }

    protected array $filters = [[]];

    /**
     * Add rule
     *
     * @param FilterRule|string|Closure(Update $update, &$value, FilterRule $rule): void $rule
     * @return $this
     */
    public function add($rule)
    {
        if($rule instanceof Closure)
        {
            $rule = new Rules\FilterCallback($rule);
        }

        return $this->and($rule);
    }

    /**
     * Add rule
     *
     * @param FilterRule|string|Closure(FilterRule): FilterRule $rule
     * @return $this
     */
    public function and($rule)
    {
        if($rule instanceof Closure)
        {
            $rule($rule = Filter::make());
        }

        if(!($rule instanceof FilterRule))
        {
            if(class_exists($class = static::class . "\\Rules\\Be" . ucfirst($rule)))
            {
                $rule = new $class;
            }
            else
            {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Filter rule should be [%s], given [%s]",
                        FilterRule::class,
                        smartTypeOf($rule),
                    )
                );
            }
        }

        $this->filters[array_key_last($this->filters)][] = $rule;

        return $this;
    }

    /**
     * Add rule (This method split before and after rules)
     *
     * `$filter->and('message')->and('text')->or()->and('callback')->and('example-callback-rule')`
     *
     * @param null|FilterRule|string|Closure(FilterRule): FilterRule $rule
     * @return $this
     */
    public function or($rule = null)
    {
        $this->filters[] = [];

        if($rule !== null)
        {
            $this->and($rule);
        }

        return $this;
    }

    /**
     * Checks filters is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->filters) == 1 && !$this->filters[0];
    }

    protected Update $lastUpdate;

    /**
     * Pass filter
     *
     * @param Update $update
     * @param        $value
     * @return void
     */
    public function pass(Update $update, &$value)
    {
        $this->lastUpdate = $update;
        $fails = [];

        // OR group
        foreach($this->filters as $group)
        {
            try
            {
                $initValue = $value;

                // AND group
                foreach($group as $rule)
                {
                    $rule->pass($update, $initValue);
                }

                // Passed
                $value = $initValue;
                return;
            }
            catch(FilterFailException $fail)
            {
                $fails[] = $fail;
            }
        }

        // Failed
        if($fails)
        {
            for($i = 0; $i < count($fails) - 1; $i++)
            {
                $fails[$i]->next = $fails[$i + 1];
            }

            // Error callback
            if($fn = $this->onError)
            {
                $value = $fn(
                    isset($this->errorImploding) ?
                        $fails[0]->implode($this->errorImploding) :
                        $fails[0]->description
                );
                return;
            }

            // Exception callback
            if($fn = $this->catchError)
            {
                $value = $fn($fails[0]);
                return;
            }

            throw $fails[0];
        }
    }

    /**
     * Apply filters and return value
     *
     * @param Update $update
     * @return mixed
     */
    public function filter(Update $update)
    {
        $value = $update;

        $this->pass($update, $value);

        return $value;
    }


    protected $onError;
    protected $errorImploding;
    protected $catchError;

    /**
     * Set error callback
     *
     * @param Closure(string $description): mixed $callback
     * @param string|null                         $implode
     * @return $this
     */
    public function error(Closure $callback, string $implode = null)
    {
        $this->onError = $callback;
        $this->errorImploding = $implode;
        $this->catchError = null;
        return $this;
    }

    /**
     * Set fail exception callback
     *
     * @param Closure(FilterFailException $fail): mixed $callback
     * @return $this
     */
    public function catch(Closure $callback)
    {
        $this->catchError = $callback;
        $this->onError = null;
        return $this;
    }

    /**
     * Return null if filter failed
     *
     * @return $this
     */
    public function nullOnFail()
    {
        return $this->catch(fn() => null);
    }

    /**
     * Catch filter fail and handle by default
     *
     * @return $this
     */
    public function catchDefault()
    {
        return $this->catch(function(FilterFailException $exception)
        {
            static::handleGlobally($exception, $this->lastUpdate);
        });
    }


    /**
     * Add message filter
     *
     * @param $messageError
     * @return $this
     */
    public function message($messageError = null)
    {
        return $this->add(new Rules\BeMessage($messageError));
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
        return $this->add(new Rules\BeText($textError, $messageError));
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
        return $this->add(new Rules\BeTextSingleLine($singleLineError, $textError, $messageError));
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
        return $this->add(new Rules\BeFloat($numberError, $messageError, $unsigned));
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
        return $this->add(new Rules\BeFloat($numberError, $messageError, true));
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
        return $this->add(new Rules\BeInt($numberError, $messageError, $unsigned));
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
        return $this->add(new Rules\BeInt($numberError, $messageError, true));
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
        return $this->add(new Rules\FilterClamp(...func_get_args()));
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
        return $this->clamp(min: $min, minError: $error);
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
        return $this->clamp(max: $max, maxError: $error);
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
        return $this->add(new Rules\FilterLength(...func_get_args()));
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
        return $this->length(min: $min, minError: $error, ascii: $ascii);
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
        return $this->length(max: $max, maxError: $error, ascii: $ascii);
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
        return $this->add(new Rules\FilterRegex($pattern, $result, $error));
    }


    protected static $globalFailHandler = DefaultFilterFailHandler::class;

    /**
     * Register filter fail handler
     *
     * @param string|Closure $handler
     * @return void
     */
    public static function registerFailHandler(string|Closure $handler)
    {
        static::$globalFailHandler = $handler;
    }

    /**
     * Handle error fail globally
     *
     * @param FilterFailException $exception
     * @param Update $update
     * @return void
     */
    public static function handleGlobally(FilterFailException $exception, Update $update)
    {
        $handler = static::$globalFailHandler;
        if(is_string($handler))
        {
            Container::getInstance()->make($handler)->handle($exception, $update);
        }
        else
        {
            (static::$globalFailHandler)($exception, $update);
        }
    }

}
