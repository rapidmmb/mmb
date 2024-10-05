<?php

namespace Mmb\Action\Road\Station\List;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatter;
use Mmb\Support\Format\KeyFormatterBuilder;

class ListPaginateViewer extends ListViewer
{

    public function __construct(
        public readonly int|Closure|null $perPage = null,
        public readonly array|Closure    $columns = ['*'],
    )
    {
    }

    public bool $needsPage = true;

    public LengthAwarePaginator $paginator;

    public function bootPagination(Builder $query) : bool
    {
        $perPage =
            $this->perPage instanceof Closure ?
                fn ($total) => $this->station->fireSign($this->perPage, $total) :
                $this->perPage;

        $columns =
            $this->columns instanceof Closure ?
                $this->station->fireSign($this->columns) :
                $this->columns;

        $this->paginator = $query->paginate($perPage, $columns, page: $this->station->page);

        $this->station->mergeDynamicArgs(
            [
                'paginator' => $this->paginator,
                'page'      => $this->station->page,
                'perPage'   => $this->paginator->perPage(),
                'total'     => $this->paginator->total(),
                'lastPage'  => $this->paginator->lastPage(),
            ]
        );

        return $this->paginator->isNotEmpty();
    }

    public function renderList(Menu $menu) : KeyFormatterBuilder
    {
        return KeyFormatter::for(
            function () use ($menu)
            {
                foreach ($this->paginator as $item)
                {
                    yield [
                        $this->station->sign->getItemKey($this->station, $menu, $item),
                    ];
                }
            }
        );
    }

    public function renderPaginator(Menu $menu) : KeyFormatterBuilder
    {
        return KeyFormatter::for(
            [
                $menu->paginateRow(
                    $this->paginator,
                    function (int $page)
                    {
                        $this->station->page = $page;
                        $this->station->fireAction('main');
                    }
                ),
            ]
        );
    }

    public function renderTitleKeyLabel() : ?string
    {
        return sprintf("صفحه %s از %s", $this->paginator->currentPage(), $this->paginator->lastPage());
    }

}