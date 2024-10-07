<?php

namespace Mmb\Action\Road;

use Closure;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\Register\InlineLoadRegister;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Road\Attributes\StationParameterResolverAttributeContract;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\Section;
use Mmb\Core\Updates\Update;
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

        public string        $name,

        Update               $update = null,
    )
    {
        parent::__construct($update);
    }

    public function menu(string $name, ...$args)
    {
        $register = $this->createInlineRegister(Menu::class, $name, $args);
        $register->inlineAction->initializer($this->road, $this->name . '.' . $name);
        return $register->register();
    }

    public function inlineForm(string $name, ...$args)
    {
        $register = $this->createInlineRegister(InlineForm::class, $name, $args);
        $register->inlineAction->initializer($this->road, $this->name . '.' . $name);
        return $register->register();
    }

    public function dialog(string $name, ...$args)
    {
        $register = $this->createInlineRegister(Dialog::class, $name, $args);
        $register->inlineAction->initializer($this->road, $this->name . '.' . $name);
        return $register->register();
    }

    protected function onInitializeInlineRegister(InlineRegister $register)
    {
        $this->initializeInlineRegister($register);

        parent::onInitializeInlineRegister($register);
    }

    public function initializeInlineRegister(InlineRegister $register)
    {
        $register->before(
            function () use ($register)
            {
                $register->inlineAction->with(...$this->road->getStationWith($this));
                $register->inlineAction->withOn('$', $this, 'ps');
                
                if ($register instanceof InlineLoadRegister)
                {
                    $this->loadPsCallData();
                }
            }
        );
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
        return $this->sign->fire($event, ...$args, ...$this->getDynamicArgs());
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
    protected function getDynamicArgs() : array
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

        if ($name == 'main')
        {
            $name = $this->defaultAction;

            $this->prepareDefaultAction($normalArgs, $dynamicArgs);
        }

        elseif ($name == 'revert')
        {
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
        foreach ($this->sign->getParams() as [$names, $callback, $resolvers])
        {
            $pass = [];
            foreach ($names as $name)
            {
                if (array_key_exists($name, $dynamicArgs))
                {
                    $value = $dynamicArgs[$name];
                    unset($dynamicArgs[$name]);

                    /** @var ?StationParameterResolverAttributeContract $resolver */
                    [$resolver, $ref] = $resolvers[$name] ?? [null, null];

                    $this->psCall[$name]
                        = $pass[$name] = $resolver ? $resolver->getStationParameterForLoad($ref, $value) : $value;
                    $this->ps[$name] = $resolver ? $resolver->getStationParameterForStore($ref, $value) : $value;
                }
                elseif (!$callback)
                {
                    throw new \InvalidArgumentException("Parameter [$name] is required");
                }
            }

            if ($callback)
            {
                $this->fireSign($callback, ...$pass);
            }
        }

        if ($dynamicArgs)
        {
            throw new \InvalidArgumentException(
                sprintf("Too many parameters, parameter [%s] is not required", array_keys($dynamicArgs)[0])
            );
        }
    }

    /**
     * Get keeps variables
     *
     * @return array
     */
    public function getKeeps() : array
    {
        return $this->keeps;
    }

    /**
     * Get keeps data
     *
     * @return array
     */
    public function keepData() : array
    {
        $data = [];
        foreach ($this->getKeeps() as $keep)
        {
            $data[$keep] = $this->$keep;
        }

        if ($this->ps)
        {
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
        foreach ($data as $name => $value)
        {
            $this->$name = $value;

            if ($name == 'ps')
            {
                $this->loadPsCallData();
            }
        }
    }

    /**
     * @return void
     */
    public function loadPsCallData()
    {
        foreach ($this->sign->getParams() as [$names, $callback, $resolvers])
        {
            foreach ($names as $name)
            {
                if (array_key_exists($name, $this->ps))
                {
                    $value = $this->ps[$name];

                    /** @var ?StationParameterResolverAttributeContract $resolver */
                    [$resolver, $ref] = $resolvers[$name] ?? [null, null];

                    $this->psCall[$name] = $resolver ? $resolver->getStationParameterForLoad($ref, $value) : $value;
                }
            }
        }

    }

}
