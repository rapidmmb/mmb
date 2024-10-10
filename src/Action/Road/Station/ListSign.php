<?php

namespace Mmb\Action\Road\Station;

use Closure;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\Station\Concerns\SignWithMenuCustomizing;
use Mmb\Action\Road\Station\List\ListViewer;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\MenuKey;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Format\KeyFormatterBuilder;

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
 * @method $this insertEmptyKey(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
 * @method $this insertEmptyRow(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX, ?bool $rtl = null)
 * @method $this insertEmptySchema(Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX, ?bool $rtl = null)
 * @method $this removeEmptyKey(string $name)
 * @method $this moveEmptyKey(string $name, ?int $x, ?int $y)
 *
 * @method $this titleKey(Closure|false $callback, int $x = 50, int $y = 0)
 * @method $this titleKeyDefault(int $x = 50, int $y = 0)
 * @method $this titleKeyAction(Closure $action)
 * @method $this titleKeyLabel(Closure $callback)
 * @method $this titleKeyLabelUsing(Closure $callback)
 * @method $this titleKeyLabelPrefix(string|Closure $string)
 * @method $this titleKeyLabelSuffix(string|Closure $string)
 *
 * @method $this emptyKey(Closure|false $callback, int $x = 50, int $y = 50)
 * @method $this emptyKeyDefault(int $x = 50, int $y = 50)
 * @method $this emptyKeyAction(Closure $action)
 * @method $this emptyKeyLabel(Closure $callback)
 * @method $this emptyKeyLabelUsing(Closure $callback)
 * @method $this emptyKeyLabelPrefix(string|Closure $string)
 * @method $this emptyKeyLabelSuffix(string|Closure $string)
 */
class ListSign extends Sign
{
    use SignWithMenuCustomizing,
        Concerns\SignWithQuery,
        Concerns\SignWithItems,
        Concerns\SignWithResponse,
        Concerns\SignWithMessage,
        Concerns\SignWithBacks;

    /**
     * Boot the default values
     *
     * @return void
     */
    protected function boot()
    {
        parent::boot();
        $this->defineKey('titleKey', 'header', 50, 0);
        $this->defineKey('emptyKey', 'empty', 50, 50);
        $this->defineKey('searchKey', 'header', 0, 100);
        $this->defineKey('searchingKey', 'header', 0, 100);
    }

    // - - - - - - - - - - - - Title Key - - - - - - - - - - - - \\

    protected function onDefaultTitleKey(Menu $menu, ListStation $station) : ?MenuKey
    {
        $label = $this->getDefinedNullableLabel($station, 'titleKeyLabel');

        if ($label === null)
            return null;

        return $menu->key(
            $label,
            fn () => $this->fireBy($station, 'titleKeyAction'),
        );
    }

    protected function onTitleKeyLabel(ListStation $station) : ?string
    {
        return $this->getViewer()->use($station)->renderTitleKeyLabel();
    }

    protected function onTitleKeyAction(ListStation $station) : void
    {
        $this->getViewer()->use($station)->onTitleKeyAction();
    }

    // - - - - - - - - - - - - Empty Key - - - - - - - - - - - - \\

    protected function onDefaultEmptyKey(Menu $menu, ListStation $station) : ?MenuKey
    {
        $label = $this->getDefinedNullableLabel($station, 'emptyKeyLabel');

        if ($label === null)
            return null;

        return $menu->key(
            $label,
            fn () => $this->fireBy($station, 'emptyKeyAction'),
        );
    }

    protected function onEmptyKeyLabel(ListStation $station) : ?string
    {
        return $this->getViewer()->use($station)->renderEmptyKeyLabel();
    }

    protected function onEmptyKeyAction(ListStation $station) : void
    {
        $this->getViewer()->use($station)->onEmptyKeyAction();
    }

    // - - - - - - - - - - - - Search Section - - - - - - - - - - - - \\

    public SearchSign $searchSign;

    /**
     * Searchable list
     *
     * @param (Closure(SearchSign): void)|false|null $callback
     * @return $this
     */
    public function searchable(Closure|null|false $callback = null)
    {
        if ($callback === false)
        {
            if (isset($this->searchSign))
            {
                $this->searchSign->die();
            }

            unset($this->searchSign);
            return $this;
        }

        $search = $this->searchSign ?? new SearchSign($this->road, $this);

        if ($callback)
        {
            $callback($search);
        }

        return $this;
    }

    // - - - - - - - - - - - - Create Station - - - - - - - - - - - - \\

    /**
     * Create list station
     *
     * @param string $name
     * @param Update $update
     * @return ListStation
     */
    public function createStation(string $name, Update $update) : Station
    {
        return new ListStation($this->road, $this, $name, $update);
    }


    // - - - - - - - - - - - - View Engine - - - - - - - - - - - - \\

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


    public const PAGINATOR_AT_NOTHING = 0;
    public const PAGINATOR_AT_TOP     = 1;
    public const PAGINATOR_AT_BOTTOM  = 2;
    public const PAGINATOR_AT_BOTH    = 3;

    protected int $paginatorAt = self::PAGINATOR_AT_BOTTOM;

    /**
     * Set the paginator position
     *
     * @param int $at
     * @return $this
     */
    public function paginatorAt(int $at)
    {
        $this->paginatorAt = $at;
        return $this;
    }

    public function paginatorHide()
    {
        return $this->paginatorAt(self::PAGINATOR_AT_NOTHING);
    }

    public function paginatorAtTop()
    {
        return $this->paginatorAt(self::PAGINATOR_AT_TOP);
    }

    public function paginatorAtBottom()
    {
        return $this->paginatorAt(self::PAGINATOR_AT_BOTTOM);
    }

    public function paginatorAtBoth()
    {
        return $this->paginatorAt(self::PAGINATOR_AT_BOTH);
    }

    /**
     * Get paginator position
     *
     * @return int
     */
    public function getPaginatorAt()
    {
        return $this->paginatorAt;
    }

    // - - - - - - - - - - - - List Formatting - - - - - - - - - - - - \\

    /**
     * Set format list using
     *
     * @param Closure(KeyFormatterBuilder): KeyFormatterBuilder $callback
     * @return $this
     */
    public function formatListUsing(Closure $callback)
    {
        return $this->listen('formatListUsing', $callback);
    }

    /**
     * Set list format by auto resizing
     *
     * @param int $size
     * @return $this
     */
    public function formatListResizeAuto(int $size = 40)
    {
        return $this->formatListUsing(
            fn (KeyFormatterBuilder $key) => $key->resizeAuto($size)
        );
    }

    /**
     * Set list format by resizing
     *
     * @param int $columns
     * @return $this
     */
    public function formatListResize(int $columns)
    {
        return $this->formatListUsing(
            fn (KeyFormatterBuilder $key) => $key->resize($columns)
        );
    }

    /**
     * Set list format by wrapping
     *
     * @param int $max
     * @return $this
     */
    public function formatListWrap(int $max)
    {
        return $this->formatListUsing(
            fn (KeyFormatterBuilder $key) => $key->wrap($max)
        );
    }

    protected function getEventOptionsOnFormatListUsing()
    {
        return [
            'call' => EventCaller::CALL_BUILDER,
        ];
    }

}