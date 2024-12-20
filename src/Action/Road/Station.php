<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineCreateRegister;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Road\Attributes\StationParameterResolverAttributeContract;
use Mmb\Action\Road\Attributes\StationPropertyResolverAttributeContract;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\Section;
use Mmb\Core\Updates\Update;
use Mmb\Support\AttributeLoader\AttributeLoader;
use Mmb\Support\Caller\Caller;

/**
 * @template T of Sign
 */
abstract class Station extends Section
{

    public function __construct(
        public readonly Road $road,
        /**
         * @var T|Sign $sign
         */
        public readonly Sign $sign,
    )
    {
        parent::__construct($road->context);
    }

    protected function onInitializeInlineRegister(InlineRegister $register)
    {
        $this->initializeInlineRegister($register);

        parent::onInitializeInlineRegister($register);
    }

    public function initializeInlineRegister(InlineRegister $register)
    {
        if ($register instanceof InlineCreateRegister) {
            $register->inlineAction->initializer($this->road, $this->sign->name . '.' . $register->method);
        }

        $register->before(
            function () use ($register) {
                $register->inlineAction->with(...$this->road->getStationWith($this));
                $register->inlineAction->withOn('$', $this, 'ps');

                if ($register instanceof InlineLoadRegister) {
                    $this->loadPsCallData();
                }
            },
        );
    }

    /**
     * Call a callback
     *
     * @param Closure $callback
     * @param ...$args
     * @return mixed
     */
    public function call(Closure $callback, ...$args)
    {
        return $this->fireSign($callback, ...$args);
    }

    /**
     * Fire a sign event
     *
     * @param string|array|Closure $event
     * @param                      ...$args
     * @return mixed
     */
    public function fireSign(string|array|Closure $event, ...$args)
    {
        return $this->sign->fire($event, ...array_merge($args, $this->getDynamicArgs()));
    }

    /**
     * Fire a sign event
     *
     * @param WeakSign $sign
     * @param string|array|Closure $event
     * @param                      ...$args
     * @return mixed
     */
    public function fireSignAs(WeakSign $sign, string|array|Closure $event, ...$args)
    {
        return $sign->fire($event, ...array_merge($args, $this->getDynamicArgs()));
    }


    protected array $dynamicArgs = [];

    /**
     * Merge dynamic arguments
     *
     * @param array $args
     * @return $this
     */
    public function mergeDynamicArgs(array $args)
    {
        $this->dynamicArgs = array_replace($this->dynamicArgs, $args);
        return $this;
    }

    /**
     * Get list of dynamic arguments
     *
     * @return array
     */
    protected function getDynamicArgs(): array
    {
        return [
            'station' => $this,
            ...$this->psCall,
            ...$this->dynamicArgs,
        ];
    }


    /**
     * Default method to run when open station
     *
     * @var string
     */
    protected string $defaultAction = 'main';

    /**
     * Revert method to run when backing from other station
     *
     * @var string|null
     */
    protected ?string $revertAction = null;

    /**
     * The variables that should save after changing the station
     *
     * @var array
     */
    protected array $keeps = [];

    /**
     * Fire an action
     *
     * @param string $name
     * @param        ...$args
     * @return mixed
     */
    public function fireAction(string $name, ...$args)
    {
        [$normalArgs, $dynamicArgs] = Caller::splitArguments($args);

        if ($name == 'main') {
            $name = $this->defaultAction;

            $this->prepareDefaultAction($normalArgs, $dynamicArgs);
        } elseif ($name == 'revert') {
            $name = $this->revertAction ?? $this->defaultAction;
        }

        return $this->invokeDynamic($name, $normalArgs, $dynamicArgs);
    }

    /**
     * Caught parameters
     *
     * @var array
     */
    public array $ps = [];

    /**
     * Parameters to pass in functions
     *
     * @var array
     */
    public array $psCall = [];

    /**
     * Prepare the default action
     *
     * @param array $normalArgs
     * @param array $dynamicArgs
     * @return void
     */
    protected function prepareDefaultAction(array $normalArgs, array $dynamicArgs)
    {
        foreach ($this->sign->getParams() as [$names, $callback, $resolvers]) {
            $pass = [];
            foreach ($names as $name) {
                if (array_key_exists($name, $dynamicArgs)) {
                    $value = $dynamicArgs[$name];
                    unset($dynamicArgs[$name]);

                    /** @var ?StationParameterResolverAttributeContract $resolver */
                    [$resolver, $ref] = $resolvers[$name] ?? [null, null];

                    $this->psCall[$name]
                        = $pass[$name] = $resolver ? $resolver->getStationParameterForLoad($this->context, $ref, $value) : $value;
                    $this->ps[$name] = $resolver ? $resolver->getStationParameterForStore($this->context, $ref, $value) : $value;
                } elseif (!$callback) {
                    throw new \InvalidArgumentException("Parameter [$name] is required");
                }
            }

            if ($callback) {
                $this->fireSign($callback, ...$pass);
            }
        }

        if ($dynamicArgs) {
            throw new \InvalidArgumentException(
                sprintf("Too many parameters, parameter [%s] is not required", array_keys($dynamicArgs)[0]),
            );
        }
    }

    /**
     * Get keeps variables
     *
     * @return array
     */
    public function getKeeps(): array
    {
        return $this->keeps;
    }

    /**
     * Get keeps data
     *
     * @return array
     */
    public function keepData(): array
    {
        $data = [];
        foreach ($this->getKeeps() as $keep) {
            $resolver = AttributeLoader::getPropertyAttributeOf(
                $this, $keep, StationPropertyResolverAttributeContract::class,
            );

            if ($resolver) {
                $data[$keep] = $resolver->getStationPropertyForStore(
                    $this->context,
                    new \ReflectionProperty($this, $keep), $this->$keep,
                );
            } else {
                $data[$keep] = $this->$keep;
            }
        }

        if ($this->ps) {
            $data['ps'] = $this->ps;
        }

        return $data;
    }

    /**
     * Revert the data
     *
     * @param array $data
     * @return void
     */
    public function revertData(array $data)
    {
        foreach ($data as $name => $value) {
            if ($name == 'ps') {
                $this->ps = $value;
                $this->loadPsCallData();
                continue;
            }

            if (property_exists($this, $name)) {
                $resolver = AttributeLoader::getPropertyAttributeOf(
                    $this, $name, StationPropertyResolverAttributeContract::class,
                );

                if ($resolver) {
                    $this->$name = $resolver->getStationPropertyForLoad($this->context, new \ReflectionProperty($this, $name), $value);
                    continue;
                }
            }

            $this->$name = $value;
        }
    }

    /**
     * @return void
     */
    public function loadPsCallData()
    {
        foreach ($this->sign->getParams() as [$names, $callback, $resolvers]) {
            foreach ($names as $name) {
                if (array_key_exists($name, $this->ps)) {
                    $value = $this->ps[$name];

                    /** @var ?StationParameterResolverAttributeContract $resolver */
                    [$resolver, $ref] = $resolvers[$name] ?? [null, null];

                    $this->psCall[$name] = $resolver ? $resolver->getStationParameterForLoad($this->context, $ref, $value) : $value;
                }
            }
        }

    }

}
