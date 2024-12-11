<?php

namespace Mmb\Action\Road;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Mmb\Action\Action;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Core\Updates\Update;
use Mmb\Support\Behavior\Behavior;
use Mmb\Support\Caller\Caller;

class Road extends Action
{

    /**
     * Make new instance
     *
     * @param Update $update
     * @return static
     */
    public static function make(Update $update)
    {
        return new static($update);
    }

    /**
     * Defined station names
     *
     * @var array
     */
    protected array $stations = [];

    /**
     * Defined station names
     *
     * @return array
     */
    protected function stations(): array
    {
        return [];
    }

    /**
     * Get list of station names
     *
     * @return array
     */
    public function getStations(): array
    {
        return [...$this->stations(), ...$this->stations];
    }

    /**
     * Merge a station
     *
     * @param string $name
     * @param Closure|null $callback
     * @return void
     */
    public final function mergeStation(string $name, Closure $callback = null)
    {
        if (isset($callback)) {
            $this->stations[$name] = $callback;
        } else {
            $this->stations[] = $name;
        }
    }

    /**
     * Defined with properties
     *
     * @var array
     */
    protected array $with = [];

    /**
     * Defined with properties
     *
     * @return array
     */
    protected function with(): array
    {
        return [];
    }

    /**
     * Get list of with properties
     *
     * @return array
     */
    public function getWith(): array
    {
        return [...$this->with(), ...$this->with];
    }

    /**
     * Get list of with properties for a station
     *
     * @param Station $station
     * @return array
     */
    public function getStationWith(Station $station): array
    {
        return ['wayStack', ...$this->getWith()];
    }

    /**
     * Merge a with property
     *
     * @param string $name
     * @return void
     */
    public final function mergeWith(string $name)
    {
        $this->with[] = $name;
    }

    /**
     * Get event callback for an inline action
     *
     * @param InlineRegister $register
     * @return Closure|null
     */
    protected function getInlineCallbackFor(InlineRegister $register)
    {
        if (str_contains($register->method, '.')) {
            [$stationName, $subName] = explode('.', $register->method, 2);

            $alias = $register->method;
            $register->method = $subName;
            $callback = $this->createHeadStation($stationName)->getInlineCallbackFor($register);
            $register->method = $alias;

            return $callback;
        }

        return parent::getInlineCallbackFor($register);
    }

    /**
     * Event on registering an inline action
     *
     * @param InlineRegister $register
     * @return void
     */
    protected function onInitializeInlineRegister(InlineRegister $register)
    {
        $this->head->initializeInlineRegister($register);

        parent::onInitializeInlineRegister($register);
    }


    private array $loadedSigns = [];

    /**
     * Get a sign
     *
     * @param string $name
     * @return Sign
     */
    public function getSignOf(string $name): Sign
    {
        if (array_key_exists($name, $this->loadedSigns)) {
            return $this->loadedSigns[$name];
        }

        $stations = $this->getStations();

        if (array_key_exists($name, $stations)) {
            $callback = $stations[$name];
        } elseif (in_array($name, $stations)) {
            if (!method_exists($this, $name)) {
                throw new \BadMethodCallException(sprintf("Station [%s] should defined by a method", $name));
            }

            $callback = $this->$name(...);
        } else {
            throw new \BadMethodCallException(sprintf("Station [%s] is not exists", $name));
        }

        $type = static::detectSignTypeFromCallback($callback);

        if (is_null($type)) {
            throw new \BadMethodCallException(sprintf("Station [%s] has not a valid sign as first parameter", $name));
        }

        $sign = $this->loadedSigns[$name] = new $type($this);
        Caller::invoke($this->context, $callback, [$sign]);
        return $sign;
    }

    /**
     * Create a station
     *
     * @param string $name
     * @return Station
     */
    public function createStation(string $name): Station
    {
        $sign = $this->getSignOf($name);

        $sign->fire('creatingStation');
        $station = $sign->createStation($name);
        $sign->fire('createdStation', station: $station);

        return $station;
    }

    /**
     * Create a station as head
     *
     * @param string $name
     * @return Station
     */
    public function createHeadStation(string $name): Station
    {
        return $this->head = $this->createStation($name);
    }

    /**
     * Detect the sign type from a callback, using the first argument
     *
     * @param Closure $callback
     * @return string|null
     */
    public static function detectSignTypeFromCallback(Closure $callback): ?string
    {
        if ($type = @(new \ReflectionFunction($callback))->getParameters()[0]?->getType()) {
            if ($type instanceof \ReflectionNamedType && $classType = $type->getName()) {
                if (is_a($classType, Sign::class, true)) {
                    return $classType;
                }
            }
        }

        return null;
    }

    /**
     * The head station
     *
     * @var Station
     */
    public Station $head;

    /**
     * Fire a station action
     *
     * @param string|Station $station
     * @param string $name
     * @param                ...$args
     * @return mixed
     */
    public function fire(string|Station $station, string $name, ...$args)
    {
        if (is_string($station)) {
            $station = $this->createStation($station);
        }

        if ($name != 'revert' && isset($this->head) && $station != $this->head) {
            $this->wayStack[] = array_filter([$this->head->name, $this->head->keepData()]);
        }

        if (!isset($this->head) || $this->head != $station) {
            $this->head = $station;
        }

        return $station->fireAction($name, ...$args);
    }

    /**
     * Fire default station action
     *
     * @param string|Station $station
     * @param                ...$args
     * @return mixed
     */
    public function fireStation(string|Station $station, ...$args)
    {
        return $this->fire($station, 'main', ...$args);
    }

    /**
     * Stack of stations that executed
     *
     * @var string[]
     */
    public array $wayStack = [];

    /**
     * Back to previous station/section
     *
     * @return void
     */
    public function fireBack(...$args)
    {
        if ($this->wayStack) {
            try {
                @[$station, $with] = array_pop($this->wayStack);

                $station = $this->createStation($station);
                $station->revertData($with ?? []);
            } catch (\Throwable) {
                goto defaultBack;
            }

            return $this->fire($station, 'revert');
        }

        defaultBack:
        return $this->invokeDynamic('back', ...Caller::splitArguments($args));
    }

    /**
     * Back
     *
     * @return void
     */
    public function back()
    {
        Behavior::back($this->context, static::class);
    }

    /**
     * Global model
     *
     * @var string|null
     */
    protected ?string $model = null;

    /**
     * Get the global query
     *
     * @return Builder|null
     */
    public function getQuery(): ?Builder
    {
        if (isset($this->model)) {
            return $this->model::query();
        }

        return null;
    }

    /**
     * Default rtl mode enabling
     *
     * @var bool|null
     */
    protected ?bool $rtl = null;

    /**
     * Get default rtl mode
     *
     * @return bool|null
     */
    public function getRtl(): ?bool
    {
        return $this->rtl;
    }

}
