<?php

namespace Mmb\Action\Filter\Rules;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mmb\Action\Filter\FilterRule;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class FilterUnique extends FilterRule
{

    public function __construct(
        public string   $table,
        public ?string  $column = null,
        public          $except = null,
        public ?Closure $query = null,
        public          $message = null,
    )
    {
    }

    public function pass(Context $context, Update $update, &$value)
    {
        // Create query builder
        if (class_exists($this->table))
        {
            $query = $this->table::query();
        }
        else
        {
            $query = DB::table($this->table);
        }

        // Add the filter
        if (isset($this->column))
        {
            $query->where($this->column, $value);
        }
        elseif (method_exists($query, 'whereKey'))
        {
            $query->whereKey($value);
        }
        else
        {
            $query->where('id', $value);
        }

        // Add except item
        if (isset($this->except))
        {
            $except = $this->except instanceof Model ? $this->except->getKey() : $this->except;

            if (method_exists($query, 'whereNotKey'))
            {
                $query->whereNotKey($except);
            }
            else
            {
                $query->where('id', '!=', $except);
            }
        }

        // Fire user callback
        if ($this->query)
        {
            $query = ($this->query)($query);
        }


        // Fail if exists
        if ($query->exists())
        {
            $this->fail($this->message ?? __('mmb::filter.unique'));
        }
    }

}