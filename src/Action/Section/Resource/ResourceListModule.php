<?php

namespace Mmb\Action\Section\Resource;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\ResourceMaker;
use Mmb\Support\Caller\Caller;

class ResourceListModule extends ResourceModule
{

    public function __construct(
        ResourceMaker $maker,
        string        $name,
        public string $model,
    )
    {
        parent::__construct($maker, $name);

        // Page label
        $this->addHeadKey(
            fn($page, $lastPage) => $this->valueOf($this->pageLabel) ??
                __('mmb::resource.default.page', ['current' => $page, 'last' => $lastPage]),
            name     : 'pageLabel',
            x        : 100,
            condition: fn() => $this->valueOf($this->pageShow),
        );
    }

    protected $query;

    /**
     * Set model query
     *
     * @param $query
     * @return $this
     */
    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Get query
     *
     * @return Builder
     */
    public function getQuery()
    {
        $query = value($this->query) ?? $this->model::query();

        foreach($this->filters as $event)
        {
            $query = $event($query);
        }

        return $query;
    }


    protected array $filters = [];

    /**
     * Add query callback
     *
     * @param Closure(Builder $query): mixed $callback
     * @return $this
     */
    public function filter(Closure $callback)
    {
        $this->filters[] = $callback;
        return $this;
    }


    protected $label;

    /**
     * Set model title
     *
     * @param string|Closure $callback
     * @return $this
     */
    public function label(string|Closure $callback)
    {
        $this->label = $callback;
        return $this;
    }

    /**
     * @param Model $record
     * @return string
     */
    public function getLabelOf(Model $record)
    {
        if(is_string($this->label))
        {
            return $record->{$this->label};
        }

        return $this->valueOf($this->label, record: $record) ?? $record->name ?? $record->getKey();
    }


    protected $message;

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->valueOf($this->message) ?? __(
            'mmb::resource.list.message',
            $this->getDynArgsOf('page', 'lastPage')
        );
    }


    protected $select;

    public function select($action)
    {
        $this->select = $action;
        return $this;
    }

    public function fireSelect($record)
    {
        $this->fireAction($this->select ?? 'info', [$record]);
    }


    protected $top = [];

    /**
     * Add top listener for menu
     *
     * @param Closure(Menu $menu, $page, $paginate):mixed $callback
     * @return $this
     */
    public function top(Closure $callback)
    {
        $this->top[] = $callback;
        return $this;
    }

    protected $bottom = [];

    /**
     * Add bottom listener for menu
     *
     * @param Closure(Menu $menu, $page, $paginate):mixed $callback
     * @return $this
     */
    public function bottom(Closure $callback)
    {
        $this->bottom[] = $callback;
        return $this;
    }


    protected $pageShow     = true;
    protected $pageLabel;
    protected $paginateShow = true;
    protected $paginateRow;
    protected $paginateOn   = 'Bottom';

    public function pageShow($condition = true)
    {
        $this->pageShow = $condition;
        return $this;
    }

    public function pageHide($condition = true)
    {
        if($condition === true)
            $this->pageShow = false;
        else
            $this->pageShow = fn() => !$this->valueOf($condition);
        return $this;
    }

    public function pageLabel($label)
    {
        $this->pageLabel = $label;
        return $this;
    }

    public function paginateShow($condition = true)
    {
        $this->paginateShow = $condition;
        return $this;
    }

    public function paginateHide($condition = true)
    {
        if($condition === true)
            $this->paginateShow = false;
        else
            $this->paginateShow = fn() => !$this->valueOf($condition);
        return $this;
    }

    public function paginateRow(Closure $callback)
    {
        $this->paginateRow = $callback;
        return $this;
    }

    public function paginateOnBoth()
    {
        $this->paginateOn = 'Both';
        return $this;
    }

    public function paginateOnTop()
    {
        $this->paginateOn = 'Top';
        return $this;
    }

    public function paginateOnBottom()
    {
        $this->paginateOn = 'Bottom';
        return $this;
    }


    protected $notFound;
    protected $notFoundLabel;

    public function notFound($callback)
    {
        $this->notFound = $callback;
        return $this;
    }

    public function notFoundLabel($label)
    {
        $this->notFoundLabel = $label;
        return $this;
    }

    public function getNotFoundLabel()
    {
        return $this->notFoundLabel ?? __('mmb::resource.list.not_found_label');
    }


    /**
     * Searchable
     *
     * @param Closure(ResourceSearchModule $search):mixed|null $init
     * @param string|null                                      $name
     * @param int                                              $x
     * @return $this
     */
    public function searchable(Closure $init = null, string $name = null, int $x = 150)
    {
        $name ??= $this->name . '.search';

        $this->module($search = new ResourceSearchModule($this->maker, $name));

        if($init)
            $init($search);

        $this->addHeadKey(
            fn() => $search->getKeyLabel(),
            fn() => $this->fireAction($name),
            'search',
            x: $x,
        );

        return $this;
    }

    /**
     * Orderable
     *
     * @param Closure(ResourceOrderModule $order):mixed|null $init
     * @param string|null                                    $name
     * @param int                                            $x
     * @return $this
     */
    public function orderable(Closure $init = null, string $name = null, int $x = 20)
    {
        $name ??= $this->name . '.order';

        $this->module($order = new ResourceOrderModule($this->maker, $name));

        if($init)
            $init($order);

        $this->addHeadKey(
            fn() => $order->getKeyLabel(),
            fn() => $this->fireAction($name),
            'order',
            x: $x,
        );

        return $this;
    }

    /**
     * Add simple filter
     *
     * @param string                                                 $name
     * @param Closure(ResourceSimpleFilterModule $filter):mixed|null $init
     * @param int|null                                               $x
     * @return $this
     */
    public function simpleFilter(string $name, Closure $init = null, int $x = null)
    {
        $this->module($filter = new ResourceSimpleFilterModule($this->maker, $name));

        if($init)
            $init($filter);

        $this->addHeadKey(
            fn() => $filter->getKeyLabel(),
            fn() => $this->fireAction($name),
            $name,
            x: $x,
        );

        return $this;
    }

    /**
     * Add trash filter for `SoftDeletes`
     *
     * @param string       $name
     * @param Closure|null $init
     * @param int|null     $x
     * @return $this
     */
    public function trashFilter(string $name = 'trash', Closure $init = null, ?int $x = null)
    {
        return $this->simpleFilter($name,
            fn (ResourceSimpleFilterModule $filter) => $filter
                ->keyLabel(
                    fn ($keyLabel) => __('mmb::resource.trash.key_label', ['label' => $keyLabel])
                )
                ->add(
                    fn () => __('mmb::resource.trash.disabled'), fn ($query) => $query->withoutTrashed()
                )
                ->add(
                    fn () => __('mmb::resource.trash.enabled'), fn ($query) => $query->onlyTrashed()
                )
                ->toggle()
                ->when($init, fn () => $this->valueOf($init, $filter)),
            $x
        );
    }

    public function creatable(Closure $init, bool $onlyFirstPage = true, ?int $x = 100, ?int $y = null)
    {
        $this->module($create = new ResourceCreateModule($this->maker, 'create', $this->model));

        $init($create);

        $this->addTopKey(
            fn() => $create->getKeyLabel(),
            fn() => $this->fireAction('create'),
            'create',
            x: $x,
            y: $y,
            condition: fn($page) => !$onlyFirstPage || $page == 1,
        );

        return $this;
    }

    protected $perPage;

    public function perPage($count)
    {
        $this->perPage = $count;
        return $this;
    }

    public function getPerPage()
    {
        return $this->valueOf($this->perPage) ?? 15;
    }

    protected $keys = [
        'head'   => [],
        'top'    => [],
        'bottom' => [],
        'back'   => [],
    ];

    public function addHeadKey(
        $label, $action = null, string $name = null, int $x = null, ?int $y = 0, $condition = true
    )
    {
        $this->addKey($label, 'head', $action, $name, $x, $y, $condition);
        return $this;
    }

    public function moveHeadKey(string $name, int $x = null, int $y = null)
    {
        $this->moveKey($name, 'head', $x, $y);
        return $this;
    }

    public function addTopKey(
        $label, $action = null, string $name = null, int $x = null, int $y = null, $condition = true
    )
    {
        $this->addKey($label, 'top', $action, $name, $x, $y, $condition);
        return $this;
    }

    public function moveTopKey(string $name, int $x = null, int $y = null)
    {
        $this->moveKey($name, 'top', $x, $y);
        return $this;
    }

    public function addBottomKey(
        $label, $action = null, string $name = null, int $x = null, int $y = null, $condition = true
    )
    {
        $this->addKey($label, 'bottom', $action, $name, $x, $y, $condition);
        return $this;
    }

    public function moveBottomKey(string $name, int $x = null, int $y = null)
    {
        $this->moveKey($name, 'bottom', $x, $y);
        return $this;
    }


    public function main()
    {
        $this->menu('listMenu')->send();
    }

    protected $inlineAliases = [
        'listMenu' => 'listMenu',
    ];

    public function listMenu(Menu $menu)
    {
        $recordsPaginate = $this->getQuery()->paginate($this->getPerPage(), page: $page = $this->getMy('page', 1));

        $this->setDynArgs(
            page: $page,
            current: $page,
            lastPage: $recordsPaginate->lastPage(),
            paginate: $recordsPaginate,
            all: $recordsPaginate,
        );

        foreach($this->top as $event)
        {
            Caller::invoke(
                $this->context,
                $event, [], [
                    'menu'      => $menu,
                    'page'      => $page,
                    'paginate' => $recordsPaginate,
                    'all'       => $recordsPaginate,
                ]
            );
        }


        if($this->valueOf($this->paginateShow))
        {
            $paginateRow = $this->valueOf($this->paginateRow, $recordsPaginate, page: $page) ?? $menu->paginateRow($recordsPaginate);
        }
        else
        {
            $paginateRow = null;
        }

        if($menu->isCreating() && $recordsPaginate->isEmpty() && $this->notFound)
        {
            $this->valueOf($this->notFound);
        }

        $menu
            ->schema($this->keyToSchema($menu, 'head', page: $page, paginate: $recordsPaginate))
            ->schema(
                function() use ($paginateRow)
                {
                    // Paginate (top)
                    if(isset($paginateRow))
                    {
                        if($this->paginateOn == 'Top' || $this->paginateOn == 'Both')
                        {
                            yield $paginateRow;
                        }
                    }
                }
            )
            ->schema($this->keyToSchema($menu, 'top', page: $page, paginate: $recordsPaginate))
            ->schema(
                function() use ($menu, $recordsPaginate, $page)
                {
                    // List
                    foreach($recordsPaginate as $record)
                    {
                        yield [$menu->key($this->getLabelOf($record), 'select', $record)];
                    }

                    if($recordsPaginate->isEmpty())
                    {
                        yield [$menu->key($this->getNotFoundLabel())];
                    }
                }
            )
            ->schema($this->keyToSchema($menu, 'bottom', page: $page, paginate: $recordsPaginate))
            ->schema(
                function() use ($paginateRow)
                {
                    // Paginate (bottom)
                    if(isset($paginateRow))
                    {
                        if($this->paginateOn == 'Bottom' || $this->paginateOn == 'Both')
                        {
                            yield $paginateRow;
                        }
                    }
                }
            )
            ->schema($this->keyToSchema($menu, 'back'))
            ->message($this->getMessage(...))
            ->on('select', $this->fireSelect(...))
            ->on(
                'page',
                function($page)
                {
                    $this->setMy('page', $page);
                    $this->menu('listMenu')->send();
                }
            );

        foreach($this->bottom as $event)
        {
            Caller::invoke(
                $this->context,
                $event, [], [
                    'menu'      => $menu,
                    'page'      => $page,
                    'paginate' => $recordsPaginate,
                    'all'       => $recordsPaginate,
                ]
            );
        }
    }

}
