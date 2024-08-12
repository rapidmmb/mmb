<?php

namespace Mmb\Action\Filter;

use Closure;
use Mmb\Core\Updates\Update;

trait Filterable
{

    protected Filter $filter;
    protected bool $passFilterResult = false;
    protected $filterCatcher = false;

    /**
     * Filter update accept
     *
     * @param Filter|FilterRule|string|Closure(Filter $filter, Update $update): void $filter
     * @return $this
     */
    public function filter($filter, bool $passResult = false)
    {
        if($filter instanceof Filter)
        {
            $this->filter = $filter;
        }
        elseif($filter instanceof Closure)
        {
            $filter($this->getFilter());
        }
        else
        {
            $this->getFilter()->add($filter);
        }

        $this->passFilterResult = $passResult;
        return $this;
    }

    /**
     * Get filter
     *
     * @return Filter
     */
    public function getFilter()
    {
        if(!isset($this->filter))
        {
            $this->filter = Filter::make();
        }

        return $this->filter;
    }

    /**
     * Use filter
     *
     * @param Closure(Filter $filter):void $callback
     * @return $this
     */
    public function useFilter($callback)
    {
        $callback($this->getFilter());
        
        return $this;
    }

    /**
     * Catch filter error
     *
     * @param $callback
     * @return $this
     */
    public function catch($callback)
    {
        $this->filterCatcher = $callback;
        return $this;
    }

    /**
     * Catch filter error by default
     *
     * @return $this
     */
    public function catchDefault()
    {
        $this->filterCatcher = null;
        return $this;
    }

    /**
     * Event for filter error message
     *
     * @param $callback
     * @return $this
     */
    public function error($callback)
    {
        return $this->catch(static function(FilterFailException $e, Update $update) use($callback)
        {
            return $callback($e->description);
        });
    }

    /**
     * Add event to display filter error message
     *
     * @param bool   $multiple
     * @param string $header
     * @param string $footer
     * @param string $prefix
     * @param string $suffix
     * @return $this
     */
    public function errorDisplay(bool $multiple = false, string $header = '', string $footer = '', string $prefix = '', string $suffix = '')
    {
        return $this->catch(function(FilterFailException $e, Update $update) use($multiple, $header, $footer, $prefix, $suffix)
        {
            $update->response(
                $multiple ?
                    $header . $prefix . $e->description . $suffix . $footer :
                    $header . $e->lines($prefix, $suffix) . $footer,
            );
        });
    }

    /**
     * Pass filter
     *
     * @param Update $update
     * @return array [$ok, $passed, $value]
     */
    protected function passFilter(Update $update)
    {
        if(isset($this->filter))
        {
            try
            {
                $result = $this->filter->filter($update);

                return [true, true, $result];
            }
            catch(FilterFailException $e)
            {
                if($fn = $this->filterCatcher)
                {
                    return [false, $fn($e, $update) !== false, null];
                }
                elseif($fn === null)
                {
                    Filter::handleGlobally($e, $update);
                    return [false, true, null];
                }

                return [false, $this->defaultFailCatch($e, $update), null];
            }
        }

        return [true, false, null];
    }

    /**
     * Default catch filter error
     *
     * @param FilterFailException $e
     * @param Update              $update
     * @return false
     */
    protected function defaultFailCatch(FilterFailException $e, Update $update)
    {
        return false;
    }

}
