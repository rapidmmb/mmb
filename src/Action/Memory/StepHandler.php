<?php

namespace Mmb\Action\Memory;

use Mmb\Action\Memory\Attributes\StepHandlerAttribute;
use Mmb\Core\Updates\Update;
use Mmb\Support\AttributeLoader\HasAttributeLoader;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class StepHandler
{
    use HasAttributeLoader;

    public function __construct(
        ?StepMemory $memory = null,
    )
    {
        if($memory)
        {
            $this->load($memory);
        }
    }

    public static function make(?StepMemory $memory = null)
    {
        return new static($memory);
    }

    /**
     * Load data from memory
     *
     * @param StepMemory $memory
     * @return void
     */
    public function load(StepMemory $memory)
    {
        $ref = new ReflectionClass($this);
        foreach($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
        {
            StepHandlerAttribute::load(
                static::getPropertyAttributesOf($property->getName(), StepHandlerAttribute::class),
                $property->getName(),
                $memory,
                $this
            );
        }
    }

    /**
     * Save data to memory
     *
     * @param StepMemory $memory
     * @return void
     */
    public function save(StepMemory $memory)
    {
        $ref = new ReflectionClass($this);
        foreach($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
        {
            StepHandlerAttribute::save(
                static::getPropertyAttributesOf($property->getName(), StepHandlerAttribute::class),
                $property->getName(),
                $memory,
                $this
            );
        }

        foreach($memory as $key => $value)
        {
            if($value === null)
            {
                $memory->forget($key);
            }
        }
    }

    /**
     * Store step to user model
     *
     * @return void
     */
    public function keep(): void
    {
        Step::set($this);
    }

    /**
     * Handle the update
     *
     * @param Update $update
     * @return void
     */
    public function handle(Update $update) : void
    {
    }

    /**
     * Handle the update in the beginning
     *
     * Set the `$update->isHandled` true, to stop handling
     *
     * @param Update $update
     * @return void
     */
    public function onBegin(Update $update) : void
    {
    }

    /**
     * Handle the update in the end
     *
     * @param Update $update
     * @return void
     */
    public function onEnd(Update $update) : void
    {
    }

    /**
     * Handle the update when step changed
     *
     * @param Update $update
     * @return void
     */
    public function onLost(Update $update)
    {
    }

    /**
     * Fire a custom event
     *
     * @param string $event
     * @param        ...$args
     * @return mixed
     */
    public function fire(string $event, ...$args)
    {
        if (method_exists($this, $fn = 'on' . $event))
        {
            return $this->$fn(...$args);
        }

        return null;
    }

}
