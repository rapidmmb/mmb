<?php

namespace Mmb\Action\Road\Station;

use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;
use Mmb\Support\Format\KeyFormatterBuilder;

/**
 * @extends Station<ListSign>
 */
class ListStation extends Station
{

    protected string $defaultAction = 'main';

    public function main()
    {
        return $this->menu('listMenu')->response();
    }

    public int $page = 1;

    public function listMenu(Menu $menu)
    {
        // Get needle variables
        $viewer = $this->sign->getViewer()->use($this);
        $customizer = clone $this->sign->getMenuCustomizer();

        // Use the required properties
        if ($viewer->needsPage)
            $menu->withOn('list', $this, 'page');

        // Boot pagination
        $isNotEmpty = $viewer->bootPagination($this->sign->getQuery($this));


        // Header
        $customizer->init($this, $menu, ['header', 'body']);

        if ($isNotEmpty)
        {
            // List Body
            $list = $viewer->renderList($menu);

            /** @var KeyFormatterBuilder $list */
            $list = $this->fireSign('formatListUsing', $list);

            if ($customizer->isRtl())
            {
                $list = $list->rtl();
            }

            $menu->schema($list->toArray());
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

}