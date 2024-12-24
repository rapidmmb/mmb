<?php

namespace Mmb\Action\Road\Station\List;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatter;
use Mmb\Support\Format\KeyFormatterBuilder;
use Mmb\Support\KeySchema\KeyboardInterface;

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

    public function bootPagination(Builder $query): bool
    {
        $perPage =
            $this->perPage instanceof Closure ?
                fn($total) => $this->station->call($this->perPage, $total) :
                $this->perPage;

        $columns =
            $this->columns instanceof Closure ?
                $this->station->call($this->columns) :
                $this->columns;

        $this->paginator = $query->paginate($perPage, $columns, page: $this->station->page);

        $this->station->mergeDynamicArgs(
            [
                'paginator' => $this->paginator,
                'page' => $this->station->page,
                'perPage' => $this->paginator->perPage(),
                'total' => $this->paginator->total(),
                'lastPage' => $this->paginator->lastPage(),
            ],
        );

        return $this->paginator->isNotEmpty();
    }

    public function renderList(KeyboardInterface $keyboard): KeyFormatterBuilder
    {
        return KeyFormatter::for(
            function () use ($keyboard) {
                $items = $this->station->sign->items->getVisibleItems($this->paginator->all());

                foreach ($items as $item) {
                    yield [
                        $item->key->makeKey($keyboard),
                    ];
                }
            },
        );
    }

    public function renderPaginator(KeyboardInterface $keyboard): KeyFormatterBuilder
    {
        return KeyFormatter::for(
            [
                $keyboard->paginateRow(
                    $this->paginator,
                    function (int $page) {
                        $this->station->page = $page;
                        $this->station->fireAction('main');
                    },
                ),
            ],
        );
    }

    public function renderTitleKeyLabel(): ?string
    {
        return sprintf("صفحه %s از %s", $this->paginator->currentPage(), $this->paginator->lastPage());
    }

}