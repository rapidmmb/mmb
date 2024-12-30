<?php

namespace Mmb\Action\Road\Station;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Mmb\Action\Road\Road;
use Mmb\Action\Road\Station;
use Mmb\Action\Road\Station\Concerns\SignWithMenuCustomizing;
use Mmb\Action\Road\Station\Words\SignBackKey;
use Mmb\Action\Road\Station\Words\SignMessage;
use Mmb\Action\Road\WeakSign;
use Mmb\Action\Section\Menu;

class FilterSelectSign extends WeakSign implements Filterable
{
    use SignWithMenuCustomizing;

    public function __construct(
        Road               $road,
        protected ListSign $listSign,
    )
    {
        parent::__construct($road);
    }

    /**
     * @var SignMessage<$this>
     */
    public SignMessage $message;

    /**
     * @var SignBackKey<$this>
     */
    public SignBackKey $back;

    /**
     * @var Words\SignExtendedQuery<$this>
     */
    public Station\Words\SignExtendedQuery $query;

    protected function boot()
    {
        parent::boot();

        $this->addKey($this->back = new SignBackKey($this));
        $this->message = new SignMessage($this);
        $this->query = new Station\Words\SignExtendedQuery($this);
    }

    public function getRoot(): Sign
    {
        return $this->listSign;
    }

    protected bool $hasNone = true;
    protected null|string|Closure $noneText = null;
    protected null|string|Closure $noneLabel = null;

    protected array $options = [];

    /**
     * Add an option
     *
     * @param string|Closure $text
     * @param Closure|null $where
     * @param Closure|null $query
     * @param string|Closure|null $label
     * @return $this
     */
    public function option(
        string|Closure      $text,
        ?Closure            $where = null,
        ?Closure            $query = null,
        null|string|Closure $label = null,
    )
    {
        $this->options[] = [$text, $label, $where, $query];
        return $this;
    }

    public function optionOrderBy(string $by, string|Closure $text, null|string|Closure $label = null)
    {
        return $this->option(
            $text,
            query: fn(Builder $query) => $query->orderBy($by),
            label: $label,
        );
    }

    public function optionOrderByDesc(string $by, string|Closure $text, null|string|Closure $label = null)
    {
        return $this->option(
            $text,
            query: fn(Builder $query) => $query->orderByDesc($by),
            label: $label,
        );
    }

    public function optionLatest(string|Closure $text, null|string|Closure $label = null)
    {
        return $this->option(
            $text,
            query: fn(Builder $query) => $query->latest(),
            label: $label,
        );
    }

    public function withNone(null|string|Closure $text = null, null|string|Closure $label = null)
    {
        $this->hasNone = true;
        $this->noneText = $text;
        $this->noneLabel = $label;
        return $this;
    }

    public function withoutNone()
    {
        $this->hasNone = false;
        return $this;
    }

    /**
     * Clear options
     *
     * @return $this
     */
    public function clear()
    {
        $this->options = [];
        return $this;
    }

    protected int $optionsPerLine = 1;

    /**
     * Set options per line ratio
     *
     * @param int $perLine
     * @return $this
     */
    public function optionsPerLine(int $perLine)
    {
        $this->optionsPerLine = $perLine;
        return $this;
    }


    public const MODE_SELECT = 1;
    public const MODE_TOGGLE = 2;

    protected int $mode = self::MODE_SELECT;

    /**
     * Set selection mode
     *
     * @param int $mode
     * @return $this
     */
    public function mode(int $mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Set mode to select
     *
     * @return $this
     */
    public function selectMode()
    {
        return $this->mode(self::MODE_SELECT);
    }

    /**
     * Set mode to toggle
     *
     * @return $this
     */
    public function toggleMode()
    {
        return $this->mode(self::MODE_TOGGLE);
    }


    public function initializeFirst(ListStation $station)
    {
    }

    public function applyOnWhere(ListStation $station, Builder $query, $value)
    {
        if (array_key_exists($value, $this->options)) {
            [$text, $label, $onWhere, $onQuery] = $this->options[$value];

            if ($onWhere) {
                $query = $this->fireBy($station, $onWhere, $query);
            }
        }

        return $query;
    }

    public function applyOnQuery(ListStation $station, Builder $query, $value)
    {
        if (array_key_exists($value, $this->options)) {
            [$text, $label, $onWhere, $onQuery] = $this->options[$value];

            if ($onWhere) {
                $query = $this->fireBy($station, $onWhere, $query);
            }
        }

        return $query;
    }

    public function fireFilter(ListStation $station)
    {
        switch ($this->mode) {
            case self::MODE_SELECT:
                $station->filterRequest($this);
                break;

            case self::MODE_TOGGLE:
                $myName = $this->listSign->getFilterableName($this);
                if (is_null($value = $this->getNextValue())) {
                    unset($station->filters[$myName]);
                } else {
                    $station->filters[$myName] = $value;
                }

                $station->main();
                break;
        }
    }

    protected function getNextValue()
    {
        $current = $station->filters[$this->listSign->getFilterableName($this)] ?? null;

        if ($current === null)
            $current = 0;
        else $current++;

        if ($current >= count($this->options)) {
            if ($this->hasNone)
                $current = null;
            else $current = 0;
        }

        if ($current === null || !count($this->options))
            return null;
        else
            return $current;
    }

    public function initializeMenu(ListStation $station, Menu $menu)
    {
        $this->getMenuCustomizer()->init($menu, ['header']);

        $menu->schema(
            function () use ($station, $menu) {
                if ($this->hasNone) {
                    $text = match (true) {
                        is_string($this->noneText)         => $this->noneText,
                        $this->noneText instanceof Closure => $this->fireBy($station, $this->noneText),
                        default                            => __('mmb::road.filter.none'),
                    };

                    yield [
                        $menu->key(
                            $text,
                            function () use ($station) {
                                unset($station->filters[$this->listSign->getFilterableName($this)]);

                                $station->main();
                            },
                        ),
                    ];
                }

                foreach ($this->options as $id => [$text, $label, $onWhere, $onQuery]) {
                    $text = match (true) {
                        is_string($text)         => $text,
                        $text instanceof Closure => $this->fireBy($station, $text),
                    };

                    yield [
                        $menu->key(
                            $text,
                            function () use ($id, $station) {
                                $station->filters[$this->listSign->getFilterableName($this)] = $id;

                                $station->main();
                            },
                        ),
                    ];
                }
            },
        );

        $this->getMenuCustomizer()->init($menu, ['body', 'footer']);
    }

    protected function setCurrentFilter(ListStation $station, $id): void
    {
        if (is_null($id))
            unset($station->filters[$this->listSign->getFilterableName($this)]);
        else
            $station->filters[$this->listSign->getFilterableName($this)] = $id;
    }

    protected function getCurrentFilter(ListStation $station)
    {
        return $station->filters[$this->listSign->getFilterableName($this)] ?? null;
    }

    public function getCurrentLabel(ListStation $station): string
    {
        @[$text, $label] = $this->options[$this->getCurrentFilter($station)];

        return match (true) {
            is_string($label)         => $label,
            $label instanceof Closure => $this->fireBy($station, $label),
            is_string($text)          => $text,
            $text instanceof Closure  => $this->fireBy($station, $text),
        };
    }
}