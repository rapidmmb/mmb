<?php

namespace Mmb\Action\Road\Station;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Str;
use Mmb\Action\Form\Input;
use Mmb\Action\Road\Customize\InputCustomizer;
use Mmb\Action\Road\Road;
use Mmb\Action\Road\WeakSign;
use Mmb\Action\Section\Menu;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Encoding\Modes\Mode;
use Mmb\Support\Encoding\Modes\StringContent;
use Mmb\Support\KeySchema\KeyboardInterface;

/**
 * @method $this message(Closure|string|StringContent|array $message)
 * @method $this messageMode(Closure|string|Mode $mode)
 * @method $this messageUsing(Closure $callback)
 * @method $this messageTextUsing(Closure $callback)
 * @method $this messagePrefix(string|StringContent|Closure $string)
 * @method $this messageSuffix(string|StringContent|Closure $string)
 *
 * @method $this resetKey(Closure|false $callback)
 * @method $this resetKeyAction(Closure $action)
 * @method $this resetKeyLabel(Closure $callback)
 * @method $this resetKeyLabelUsing(Closure $callback)
 * @method $this resetKeyLabelPrefix(string|Closure $string)
 * @method $this resetKeyLabelSuffix(string|Closure $string)
 *
 * @method $this searchKey(Closure|false $callback, int $x = 50, int $y = 50)
 * @method $this searchKeyDefault(int $x = 50, int $y = 50)
 * @method $this searchKeyAction(Closure $action)
 * @method $this searchKeyLabel(Closure $callback)
 * @method $this searchKeyLabelUsing(Closure $callback)
 * @method $this searchKeyLabelPrefix(string|Closure $string)
 * @method $this searchKeyLabelSuffix(string|Closure $string)
 *
 * @method $this searchingKey(Closure|false $callback, int $x = 50, int $y = 50)
 * @method $this searchingKeyDefault(int $x = 50, int $y = 50)
 * @method $this searchingKeyAction(Closure $action)
 * @method $this searchingKeyLabel(Closure $callback)
 * @method $this searchingKeyLabelUsing(Closure $callback)
 * @method $this searchingKeyLabelPrefix(string|Closure $string)
 * @method $this searchingKeyLabelSuffix(string|Closure $string)
 */
class SearchSign extends WeakSign
{
    use Concerns\SignWithFormCustomizing,
        Concerns\SignWithQueryFilters,
        Concerns\SignWithMessage,
        Concerns\SignWithBacks;

    public function __construct(Road $road, public readonly ListSign $listSign)
    {
        parent::__construct($road);
    }

    protected function boot()
    {
        parent::boot();

        $this->insertInput('search', $this->searchInput(...), customize: $this->searchCustomizeInput(...));

        $this->defineProxyKey($this->listSign, 'searchKey', 'header', 100, 0);
        $this->defineProxyKey($this->listSign, 'searchingKey', 'header', 100, 0);
        $this->defineMessage('message');
        $this->defineDynamicKey('resetKey');
    }

    protected function shutdown()
    {
        parent::shutdown();

        $this->shutdownProxyKey($this->listSign, 'searchKey', 'header');
        $this->shutdownProxyKey($this->listSign, 'searchingKey', 'header');
    }

    // - - - - - - - - - - - - Search Input - - - - - - - - - - - - \\

    protected function searchCustomizeInput(InputCustomizer $input)
    {
        $input
            ->insertKey(
                'body',
                fn (KeyboardInterface $menu, ListStation $station) => $this->getDefinedDynamicKey($station, 'resetKey', $menu),
                'resetKey',
                50,
                0,
            );
    }

    protected function searchInput(Input $input, ListStation $station)
    {
        $input
            ->prompt(fn () => $this->getDefinedMessage($station, 'message'))
            ->textSingleLine();
    }

    protected function onMessage()
    {
        return __('mmb::road.search.message');
    }

    protected function onBackKeyAction(ListStation $station)
    {
        $station->searchCancel();
    }

    // - - - - - - - - - - - - Search Key - - - - - - - - - - - - \\

    protected function onDefaultSearchKey(Menu $menu, ListStation $station)
    {
        return $menu->key(
            $this->getDefinedLabel($station, 'searchKeyLabel'),
            fn () => $this->fireBy($station, 'searchKeyAction'),
        )->if($station->search === null);
    }

    protected function onDefaultSearchingKey(Menu $menu, ListStation $station)
    {
        return $menu->key(
            $this->getDefinedLabel($station, 'searchingKeyLabel'),
            fn () => $this->fireBy($station, 'searchingKeyAction'),
        )->if($station->search !== null);
    }

    protected function onSearchKeyLabel()
    {
        return __('mmb::road.search.search_key');
    }

    protected function onSearchingKeyLabel(ListStation $station)
    {
        return __(
            'mmb::road.search.searching_key',
            [
                'text'      => Str::limit($station->search, 20),
                'full_text' => $station->search,
            ]
        );
    }

    protected function onSearchKeyAction(ListStation $station)
    {
        $station->searchRequest();
    }

    protected function onSearchingKeyAction(ListStation $station)
    {
        $this->onSearchKeyAction($station);
    }

    protected function onSearchKeyVia($callback, ListStation $station)
    {
        return $callback()?->if($station->search === null);
    }

    protected function onSearchingKeyVia($callback, ListStation $station)
    {
        return $callback()?->if($station->search !== null);
    }

    // - - - - - - - - - - - - Reset Key - - - - - - - - - - - - \\

    protected function onResetKey(Input $input, ListStation $station)
    {
        return $input->keyAction(
            $this->getDefinedLabel($station, 'resetKeyLabel'),
            fn () => $this->fireBy($station, 'resetKeyAction')
        );
    }

    protected function onResetKeyLabel()
    {
        return __('mmb::road.search.reset_key');
    }

    protected function onResetKeyAction(ListStation $station)
    {
        $station->searchFinished(null);
    }

    // - - - - - - - - - - - - Query Filtering - - - - - - - - - - - - \\

    protected function getEventOptionsOnQueryUsing()
    {
        return [
            'call'    => EventCaller::CALL_BUILDER,
            'default' => EventCaller::DEFAULT_WHEN_NOT_LISTENING,
        ];
    }

    protected function onQueryUsing(Builder $query, ListStation $station)
    {
        // It's default search engine. Not bad for lazy developers.
        if ($query instanceof \Illuminate\Contracts\Database\Eloquent\Builder)
        {
            return $query->whereKey($station->search);
        }

        return $query->where('id', $station->search);
    }

    /**
     * Use `like` search engine
     *
     * @param string $column
     * @return $this
     */
    public function useLike(string $column)
    {
        $this->removeListeners('queryUsing');

        return $this->queryUsing(
            function (Builder $query, ListStation $station) use ($column)
            {
                return $query->where($column, 'LIKE', "%{$station->search}%");
            }
        );
    }

}
