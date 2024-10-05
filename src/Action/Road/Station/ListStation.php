<?php

namespace Mmb\Action\Road\Station;

use Mmb\Action\Road\Station;
use Mmb\Action\Section\Menu;

/**
 * @extends Station<ListSign>
 */
class ListStation extends Station
{

    public int $page = 1;

    public function listMenu(Menu $menu)
    {
        $viewer = $this->sign->getViewer()->use($this);
        $customizer = clone $this->sign->getMenuCustomizer();

        if ($viewer->needsPage)
            $menu->withOn('list', $this, 'page');

        $query = $this->fireSign('queryUsing');
        $query = $this->fireSign('query', $query);

        $body = $viewer->render($menu, $query);

        $customizer->init($this, $menu, ['header', 'body']);
        $menu->schema($body->toArray());
        $customizer->init($this, $menu, ['footer']);
    }

}