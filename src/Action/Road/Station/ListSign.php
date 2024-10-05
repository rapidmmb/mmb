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
 * @method $this emptyKey(Closure|false $callback)
 * @method $this emptyKeyAction(Closure $action)
 * @method $this emptyKeyLabel(Closure $callback)
 * @method $this emptyKeyLabelUsing(Closure $callback)
 * @method $this emptyKeyLabelPrefix(string|Closure $string)
 * @method $this emptyKeyLabelSuffix(string|Closure $string)
 */
class ListSign extends Sign
{
    use HasMenuCustomizing,
        Concerns\SignWithQuery,
        Concerns\SignWithItems,
        Concerns\SignWithResponse,
        Concerns\SignWithMessage;

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
    }

    // - - - - - - - - - - - - Title Key - - - - - - - - - - - - \\

    protected function onDefaultTitleKey(Menu $menu, ListStation $station) : ?MenuKey
    {
        $label = $this->getDefinedNullableLabel($station, 'titleKeyLabel');

        if ($label === null)
            return null;

        return $menu->key(
            $label,
            fn () => $station->fireSign('titleKeyAction'),
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
            fn () => $station->fireSign('emptyKeyAction'),
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

    protected function onEventOptionsOnFormatListUsing()
    {
        return [
            'call' => EventCaller::CALL_BUILDER,
        ];
    }

}