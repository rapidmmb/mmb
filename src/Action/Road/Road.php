<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Action\Action;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineRegister;

class Road extends Action
{

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
    protected function stations() : array
    {
        return [];
    }

    /**
     * Get list of station names
     *
     * @return array
     */
    public function getStations() : array
    {
        return [...$this->stations(), ...$this->stations];
    }

    /**
     * Merge a station
     *
     * @param string       $name
     * @param Closure|null $callback
     * @return void
     */
    public final function mergeStation(string $name, Closure $callback = null)
    {
        if (isset($callback))
        {
            $this->stations[$name] = $callback;
        }
        else
        {
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
    protected function with() : array
    {
        return [];
    }

    /**
     * Get list of with properties
     *
     * @return array
     */
    public function getWith() : array
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
        return ['curStation', ...$this->getWith()];
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
        if (str_contains($register->method, '.'))
        {
            [$stationName, $subName] = explode('.', $register->method, 2);

            $alias = $register->method;
            $register->method = $subName;
            $this->createStation($stationName)->getInlineCallbackFor($register);
            $register->method = $alias;
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
        $register->before(
            function(InlineAction $inline)
            {
                $inline->with('curStation', ...$this->getWith());
            }
        );

        parent::onInitializeInlineRegister($register);
    }


    private array $loadedSigns = [];

    /**
     * Get a sign
     *
     * @param string $name
     * @return Sign
     */
    public function getSignOf(string $name) : Sign
    {
        if (array_key_exists($name, $this->loadedSigns))
        {
            return $this->loadedSigns[$name];
        }

        $stations = $this->getStations();

        if (array_key_exists($name, $stations))
        {
            $callback = $stations[$name];
        }
        elseif (in_array($name, $stations))
        {
            if (!method_exists($this, $name))
            {
                throw new \BadMethodCallException(sprintf("Station [%s] should defined by a method", $name));
            }

            $callback = $this->$name(...);
        }
        else
        {
            throw new \BadMethodCallException(sprintf("Station [%s] is not exists", $name));
        }

        $type = static::detectSignTypeFromCallback($callback);

        if (is_null($type))
        {
            throw new \BadMethodCallException(sprintf("Station [%s] has not a valid sign as first parameter", $name));
        }

        return $this->loadedSigns[$name] = new $type();
    }

    /**
     * Create a station
     *
     * @param string $name
     * @return Station
     */
    public function createStation(string $name) : Station
    {
        $sign = $this->getSignOf($name);

        $sign->fire('creatingStation');
        $station = $sign->createStation($name, $this->update);
        $sign->fire('createdStation', station: $station);

        return $station;
    }

    /**
     * Detect the sign type from a callback, using the first argument
     *
     * @param Closure $callback
     * @return string|null
     */
    public static function detectSignTypeFromCallback(Closure $callback) : ?string
    {
        if ($type = @(new \ReflectionFunction($callback))->getParameters()[0]?->getType())
        {
            if ($type instanceof \ReflectionNamedType && $classType = $type->getName())
            {
                if (is_a($classType, Sign::class))
                {
                    return $classType;
                }
            }
        }

        return null;
    }

    /**
     * Current station name
     *
     * @var string
     */
    public string $curStation = '';

    /**
     * Fire a station action
     *
     * @param string $station
     * @param string $name
     * @param        ...$args
     * @return mixed
     */
    public function fire(string $station, string $name, ...$args)
    {
        return $this->createStation($station)->fireAction($name, ...$args);
    }

    /**
     * Fire default station action
     *
     * @param string $station
     * @param        ...$args
     * @return mixed
     */
    public function fireStation(string $station, ...$args)
    {
        return $this->fire($station, 'main', ...$args);
    }

    /**
     * Set the current station name
     *
     * @param string $station
     * @return void
     */
    public function setCurrentStation(string $station)
    {
        $this->curStation = $station;
    }

}
