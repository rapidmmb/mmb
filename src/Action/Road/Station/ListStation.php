<?php

namespace Mmb\Action\Road\Station;

use Mmb\Action\Form\Form;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatterBuilder;

/**
 * @extends Station<ListSign>
 */
class ListStation extends Station
{

    // - - - - - - - - - - - - Properties - - - - - - - - - - - - \\

    protected function getWith()
    {
        if (isset($this->sign->searchSign))
            yield 'search';
    }

    // - - - - - - - - - - - - Main Menu - - - - - - - - - - - - \\

    protected string $defaultAction = 'main';

    public function main()
    {
        return $this->menu('listMenu')->response();
    }

    public int $page = 1;

    public function listMenu(Menu $menu)
    {
        $menu->withOn('$', $this, ...$this->getWith());

        // Get needle variables
        $viewer = $this->sign->getViewer()->use($this);
        $customizer = clone $this->sign->getMenuCustomizer();

        // Use the required properties
        if ($viewer->needsPage)
            $menu->withOn('$', $this, 'page');

        // Create query
        $query = $this->sign->getQuery($this);

        if (isset($this->search))
            $query = $this->sign->searchSign->getFilteredQuery($this, $query);

        // Boot pagination
        $isNotEmpty = $viewer->bootPagination($query);


        // Header
        $customizer->init($this, $menu, ['header', 'body']);

        if ($isNotEmpty)
        {
            // List Body
            $list = $viewer->renderList($menu);

            /** @var KeyFormatterBuilder $list */
            $list = $this->fireSign('formatListUsing', $list);

            $paginator = $viewer->renderPaginator($menu);
            $paginatorAt = $this->sign->getPaginatorAt();

            if ($customizer->isRtl())
            {
                $list = $list->rtl();
                $paginator = $paginator->rtl();
            }

            if ($paginatorAt & ListSign::PAGINATOR_AT_TOP)
            {
                $menu->schema($paginator->toArray());
            }

            $menu->schema($list->toArray());

            if ($paginatorAt & ListSign::PAGINATOR_AT_BOTTOM)
            {
                $menu->schema($paginator->toArray());
            }
        }
        else
        {
            // Empty Body
            $customizer->init($this, $menu, ['empty']);
        }

        // Footer
        $customizer->init($this, $menu, ['footer']);

        // Set the response
        $menu->responseUsing(fn ($args) => $this->fireSign('response', $args));
        $menu->message(fn () => $this->sign->getMessage($this));
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

        $formCustomizer = $this->sign->searchSign->getFormCustomizer();
        $formCustomizer->init($this, $form);

        $form->finish(
            function (Form $form)
            {
                $this->searchFinished($form->search);
            }
        );

        // Set the response
        // $menu->responseUsing(fn ($args) => $this->fireSignAs($this->sign->searchSign, 'response', $args));
        // $form->form->listen('request', fn () => $this->sign->searchSign->getMessage($this));
    }

}