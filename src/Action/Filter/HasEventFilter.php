<?php

namespace Mmb\Action\Filter;

use Mmb\Core\Updates\Update;

trait HasEventFilter
{

    protected array $onFilters = [];

    /**
     * Add on filter event
     *
     * @param $filter
     * @param $data
     * @return void
     */
    protected function addFilterEvent($filter, $data)
    {
        if(!($filter instanceof Filter))
        {
            $filter = Filter::make()->add($filter);
        }

        $this->onFilters[] = [$filter, $data];
    }

    /**
     * Find matched filter
     *
     * @param Update $update
     * @param        $data
     * @param        $value
     * @return bool
     */
    protected function getMatchedFilter(Update $update, &$data = null, &$value = null)
    {
        foreach($this->onFilters as $event)
        {
            [$filter, $data0] = $event;
            try
            {
                $value = $filter->filter($update);
                $data = $data0;
                return true;
            }
            catch(FilterFailException $e) { }
        }

        return false;
    }

}
