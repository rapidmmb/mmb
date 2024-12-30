<?php

namespace Mmb\Action\Road\Station;

use Illuminate\Contracts\Database\Query\Builder;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\DefferInlineProxy;
use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatterBuilder;

/**
 * @extends Station<ListSign>
 *
 * @property DefferInlineProxy<Menu> $listMenu
 */
class ListStation extends Station
{

    public ?SearchSign $searchSign;

    public int $paginatorAt;

    public function reset()
    {
    }

    // - - - - - - - - - - - - Properties - - - - - - - - - - - - \\

    protected function getWith()
    {
        if (isset($this->searchSign))
            yield 'search';
        if ($this->sign->getFilterables())
            yield 'filters';
    }

    // - - - - - - - - - - - - Main Menu - - - - - - - - - - - - \\

    protected string $defaultAction = 'mainInit';

    public function mainInit()
    {
        foreach ($this->sign->getFilterables() as $filterable) {
            $filterable->initializeFirst($this);
        }

        $this->main();
    }

    public function main()
    {
        $this->page = 1;
        $this->listMenu->response();
    }

    public int $page = 1;

    public function listMenu(Menu $menu)
    {
        $menu->withOn('$', $this, ...$this->getWith());

        // Get needle variables
        $viewer = $this->sign->getViewer();
        $customizer = $this->sign->getMenuCustomizer();

        // Use the required properties
        if ($viewer->needsPage) {
            $menu->withOn('$', $this, 'page');
        }

        // Create query
        $query = $this->sign->query->getQuery();

        if (isset($this->searchSign)) {
            $query = $this->searchSign->getFilteredQuery($this, $query); // todo
        }

        // Boot pagination
        $isNotEmpty = $viewer->bootPagination($query);


        // Header
        $customizer->init($menu, ['header', 'body']);

        if ($isNotEmpty) {
            // List Body
            $list = $viewer->renderList($menu);

            /** @var KeyFormatterBuilder $list */
            $list = $this->fireSign('formatListUsing', $list);

            $paginator = $viewer->renderPaginator($menu);

            if ($customizer->isRtl()) {
                $list = $list->rtl();
                $paginator = $paginator->rtl();
            }

            if ($this->paginatorAt & ListSign::PAGINATOR_AT_TOP) {
                $menu->schema($paginator->toArray());
            }

            $menu->schema($list->toArray());

            if ($this->paginatorAt & ListSign::PAGINATOR_AT_BOTTOM) {
                $menu->schema($paginator->toArray());
            }
        } else {
            // Empty Body
            $customizer->init($menu, ['empty']);
        }

        // Footer
        $customizer->init($menu, ['footer']);

        // Set the response
        $menu->responseUsing($this->sign->response->response(...));
        $menu->message(function () {
            return $this->sign->message->getMessage();
        });
    }

    // - - - - - - - - - - - - Search Form - - - - - - - - - - - - \\

    public ?string $search = null;

    public function searchFinished(?string $search)
    {
        $this->search = $search;
        $this->main();
    }

    public function searchRequest()
    {
        $this->inlineForm('searchForm')->request();
    }

    public function searchCancel()
    {
        $this->main();
    }

    public function searchForm(InlineForm $form)
    {
        $form->withOn('$', $this, ...$this->getWith());

        $formCustomizer = $this->searchSign->getFormCustomizer();
        $formCustomizer->init($form);

        $form->finish(
            function (Form $form) {
                $this->searchFinished($form->search);
            },
        );

        // Set the response
        // $menu->responseUsing(fn ($args) => $this->fireSignAs($this->sign->searchSign, 'response', $args));
        // $form->form->listen('request', fn () => $this->sign->searchSign->getMessage($this));
    }

    // - - - - - - - - - - - - Filter Section - - - - - - - - - - - - \\

    public array $filters = [];

    public function applyFilters(Builder $query)
    {
        foreach ($this->filters as $name => $value) {
            if (($value === null) || !($filterable = $this->sign->getFilterable($name))) {
                unset($this->filters[$name]);
                continue;
            }

            $filterable->applyOnWhere($this, $query, $value);
            $filterable->applyOnQuery($this, $query, $value);
        }
    }

    public function fireFilter(string $name)
    {
        if ($filterable = $this->sign->getFilterable($name)) {
            $filterable->fireFilter($this);
        } else {
            $this->main();
        }
    }

    public function filterRequest(Filterable $filterable)
    {
        if ($name = $this->sign->getFilterableName($filterable)) {
            $this->menu('filterMenu', fName: $name)->response();
        } else {
            $this->main();
        }
    }

    public function filterMenu(Menu $menu, string $fName)
    {
        if (!$filterable = $this->sign->getFilterable($fName)) {
            // TODO: Exception
            // $this->main();
            // return;
        }

        $filterable->initializeMenu($this, $menu);
    }

}