<?php

namespace Mmb\Action\Filter\Rules;

use Closure;
use Illuminate\Support\Facades\DB;
use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class FilterExists extends FilterRule
{

    public function __construct(
        public string   $table,
        public ?string  $column = null,
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

        // Fire user callback
        if ($this->query)
        {
            $query = ($this->query)($query);
        }


        // Fail if not exists
        if (!$query->exists())
        {
            $this->fail($this->message ?? __('mmb::filter.exists'));
        }
    }

}