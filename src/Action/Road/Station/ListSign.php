<?php

namespace Mmb\Action\Road\Station;

use Closure;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\Station\Concerns\SignWithMenuCustomizing;
use Mmb\Action\Road\Station\List\ListViewer;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\MenuKey;
use Mmb\Support\Caller\EventCaller;
use Mmb\Support\Format\KeyFormatterBuilder;

class ListSign extends Sign
{
    use SignWithMenuCustomizing,
        Concerns\SignWithQuery,
        Concerns\SignWithItems,
        Concerns\SignWithResponse,
        Concerns\SignWithBacks;

    public Station\Words\SignMessage $message;
    public Station\Words\SignKey $titleKey;
    public Station\Words\SignKey $emptyKey;
    public Station\Words\SignKey $searchKey;
    public Station\Words\SignKey $searchingKey;

    /**
     * Boot the default values
     *
     * @return void
     */
    protected function boot()
    {
        parent::boot();

        // Title key
        $this->addKey($this->titleKey = new Station\Words\SignKey($this));
        $this->titleKey->at(50, 0, 'header');
        $this->titleKey->label(function () {
            return $this->getViewer()->renderTitleKeyLabel();
        });
        $this->titleKey->action(function () {
            $this->getViewer()->onTitleKeyAction();
        });

        // Empty key
        $this->addKey($this->emptyKey = new Station\Words\SignKey($this));
        $this->emptyKey->at(50, 50, 'empty');
        $this->emptyKey->label(function () {
            return $this->getViewer()->renderEmptyKeyLabel();
        });
        $this->emptyKey->action(function () {
            $this->getViewer()->onEmptyKeyAction();
        });

        $this->addKey($this->searchKey = new Station\Words\SignKey($this));
        $this->searchKey->at(0, 100, 'header');

        $this->addKey($this->searchingKey = new Station\Words\SignKey($this));
        $this->searchingKey->at(0, 100, 'header');

        $this->message = new Station\Words\SignMessage($this);
        $this->message->set(function () {
            return "List:"; // todo
        });
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
        if (isset($this->searchSign)) {
            $this->searchSign->die();
        }

        if ($callback === false) {
            unset($this->searchSign);
            return $this;
        }

        $search = $this->searchSign ??= new SearchSign($this->road, $this);

        if ($callback) {
            $callback($search);
        }

        return $this;
    }

    // - - - - - - - - - - - - Create Station - - - - - - - - - - - - \\

    protected ListStation $_station;

    public function getStation(): ListStation
    {
        return new ListStation($this->road, $this);
    }

    /**
     * Create list station
     *
     * @return void
     */
    public function resetStation(): void
    {
        $station = $this->getStation();
        
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
        if (is_string($viewer)) {
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
    public function getViewer(): ListViewer
    {
        return ($this->viewer ??= new Station\List\ListPaginateViewer())->use($this->getStation());
    }

    /**
     * Show the result as pagination
     *
     * @param Closure|int|null $perPage
     * @param Closure|array $columns
     * @return $this
     */
    public function paginate(Closure|int|null $perPage = null, Closure|array $columns = ['*'])
    {
        return $this->view(new Station\List\ListPaginateViewer($perPage, $columns));
    }


    public const PAGINATOR_AT_NOTHING = 0;
    public const PAGINATOR_AT_TOP = 1;
    public const PAGINATOR_AT_BOTTOM = 2;
    public const PAGINATOR_AT_BOTH = 3;

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
            fn(KeyFormatterBuilder $key) => $key->resizeAuto($size),
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
            fn(KeyFormatterBuilder $key) => $key->resize($columns),
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
            fn(KeyFormatterBuilder $key) => $key->wrap($max),
        );
    }

    protected function getEventOptionsOnFormatListUsing()
    {
        return [
            'call' => EventCaller::CALL_BUILDER,
        ];
    }

    // - - - - - - - - - - - - Filters - - - - - - - - - - - - \\

    protected static function detectFilterableTypeFromCallback(Closure $callback): ?string
    {
        if ($type = @(new \ReflectionFunction($callback))->getParameters()[0]?->getType()) {
            if ($type instanceof \ReflectionNamedType && $classType = $type->getName()) {
                if (is_a($classType, Filterable::class, true)) {
                    return $classType;
                }
            }
        }

        return null;
    }

    protected array $filters = [];

    /**
     * Add a filter
     *
     * @param string $name
     * @param Filterable|Closure $filter
     * @return $this
     */
    public function filter(
        string             $name,
        Filterable|Closure $filter,
    )
    {
        if (array_key_exists($name, $this->filters)) {
            throw new \InvalidArgumentException("Filter [$name] is already exists");
        }

        if ($filter instanceof Closure) {
            $callback = $filter;
            if (!$type = static::detectFilterableTypeFromCallback($callback)) {
                throw new \InvalidArgumentException("Filter callback has not a valid filterable type");
            }

            $filter = new $type($this->road, $this);
            $callback($filter);
        }

        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * Get a filterable value
     *
     * @param string $name
     * @return Filterable|null
     */
    public function getFilterable(string $name): ?Filterable
    {
        return $this->filters[$name] ?? null;
    }

    /**
     * Get all filterable
     *
     * @return Filterable[]
     */
    public function getFilterables()
    {
        return $this->filters;
    }

    /**
     * Get a filter name
     *
     * @param Filterable $filterable
     * @return string|null
     */
    public function getFilterableName(Filterable $filterable): ?string
    {
        $key = array_search($filterable, $this->filters, true);
        return $key === false ? null : $key;
    }

}