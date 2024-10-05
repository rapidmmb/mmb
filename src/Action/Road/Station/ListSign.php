<?php

namespace Mmb\Action\Road\Station;

use Closure;
use Mmb\Action\Road\Customize\Concerns\HasMenuCustomizing;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\Station\List\ListViewer;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\MenuKey;
use Mmb\Core\Updates\Update;

/**
 * @method $this insertHeaderKey(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
 * @method $this insertHeaderRow(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX, ?bool $rtl = null)
 * @method $this insertHeaderSchema(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX, ?bool $rtl = null)
 * @method $this removeHeaderKey(string $name)
 * @method $this moveHeaderKey(string $name, ?int $x, ?int $y)
 *
 * @method $this insertFooterKey(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
 * @method $this insertFooterRow(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX, ?bool $rtl = null)
 * @method $this insertFooterSchema(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX, ?bool $rtl = null)
 * @method $this removeFooterKey(string $name)
 * @method $this moveFooterKey(string $name, ?int $x, ?int $y)
 *
 * @method $this titleKey(Closure|false $callback, int $x = 50, int $y = 0)
 * @method $this titleKeyDefault(int $x = 50, int $y = 0)
 * @method $this titleKeyAction(Closure $action)
 * @method $this titleKeyLabel(Closure $callback)
 * @method $this titleKeyLabelUsing(Closure $callback)
 * @method $this titleKeyLabelPrefix(string|Closure $string)
 * @method $this titleKeyLabelSuffix(string|Closure $string)
 */
class ListSign extends Sign
{
    use HasMenuCustomizing,
        Concerns\SignWithQuery,
        Concerns\SignWithItems;

    /**
     * Boot the default values
     *
     * @return void
     */
    protected function boot()
    {
        parent::boot();
        $this->defineKey('titleKey', 'header', 50, 0);
    }

    protected function onDefaultTitleKey(Menu $menu, Station $station) : ?MenuKey
    {
        return $this->getViewer()->use($station)->renderTitleKey($menu);
    }

    public function getTitleKeyAction(Station $station) : Closure
    {
        return fn () => $station->fireSign('titleKeyAction');
    }


    /**
     * Create list station
     *
     * @param Update $update
     * @return ListStation
     */
    public function createStation(Update $update) : Station
    {
        return new ListStation($this->road, $this, $update);
    }


    protected ListViewer $viewer;

    /**
     * Set list viewer instance
     *
     * @param string|ListViewer $viewer
     * @return $this
     */
    public function view(string|ListViewer $viewer)
    {
        if (is_string($viewer))
        {
            $viewer = new $viewer;
        }

        $this->viewer = $viewer;

        return $this;
    }

    /**
     * Get list viewer
     *
     * @return ListViewer
     */
    public function getViewer() : ListViewer
    {
        return $this->viewer ??= new Station\List\ListPaginateViewer();
    }

    /**
     * Show the result as pagination
     *
     * @param Closure|int|null $perPage
     * @param Closure|array    $columns
     * @return $this
     */
    public function paginate(Closure|int|null $perPage = null, Closure|array $columns = ['*'])
    {
        return $this->view(new Station\List\ListPaginateViewer($perPage, $columns));
    }

}