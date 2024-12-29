<?php

namespace Mmb\Action\Station;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Menu;
use RectorPrefix202408\Illuminate\Contracts\Database\Query\Builder;

class ListStation extends Menu
{

    public function register(InlineRegister $register)
    {
        parent::register($register);

        $register->after(function () {

            if (!$this->paginator->isEmpty()) {
                $this->schema(function () {
                    foreach ($this->paginator->items() as $item) {
                        yield ($this->item)($item);
                    }
                });
            }

        });
    }

    public int $page;

    protected Paginator $paginator;

    public function from(string|Builder|Closure $query, int $perPage = 15, ?int $page = null)
    {
        /** @var Builder $query */
        $query = value($query);
        $query = is_string($query) ? $query::query() : $query;

        if (!isset($page)) {
            $this->have('page', $page, 1);
        }

        $this->page = $page;
        $this->paginator = $query->paginate(perPage: $perPage, page: $page);

        return $this;
    }

    protected Closure $item;

    public function item(Closure $callback)
    {
        $this->item = $callback;
        return $this;
    }

    public function empty(Closure $callback)
    {
        if ($this->paginator->isEmpty()) {
            $callback();
        }
    }

}