<?php

namespace Mmb\Action\Filter\Rules;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class FilterUnique extends FilterRule
{

    public function __construct(
        public string   $table,
        public ?string  $column = null,
        public $expect = null,
        public ?Closure $query = null,
        public $message = null,
    )
    {
    }

    public function pass(Update $update, &$value)
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

        // Add expect item
        if (isset($this->expect))
        {
            $expect = $this->expect instanceof Model ? $this->expect->getKey() : $this->expect;

            if (method_exists($query, 'whereNotKey'))
            {
                $query->whereNotKey($expect);
            }
            else
            {
                $query->where('id', '!=', $expect);
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