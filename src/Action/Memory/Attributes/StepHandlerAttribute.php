<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;
use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\StepMemory;

#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class StepHandlerAttribute
{

    public string $name;

    public StepMemory $memory;

    public StepHandler $handler;

    public bool $preventDefault = false;

    public function init(
        string      $name,
        StepMemory  $memory,
        StepHandler $handler,
    )
    {
        $this->name = $name;
        $this->memory = $memory;
        $this->handler = $handler;
    }

    /**
     * Get alias name for save/load
     *
     * @return ?string
     */
    public function getAlias()
    {
        return null;
    }

    /**
     * Event for loading data
     *
     * @return void
     */
    public function beforeLoad()
    {
    }

    /**
     * Event for loading data from memory
     *
     * @param $data
     * @return mixed
     */
    public function onLoad($data)
    {
        return $data;
    }

    /**
     * Event for saving data
     *
     * @return void
     */
    public function beforeSave()
    {
    }

    /**
     * Event for saving data to memory
     *
     * @param $data
     * @return mixed
     */
    public function onSave($data)
    {
        return $data;
    }

    /**
     * Save property data
     *
     * @param StepHandlerAttribute[] $attrs
     * @param string $name
     * @param StepMemory $memory
     * @param StepHandler $handler
     * @return void
     */
    public static function save(
        array       $attrs,
        string      $name,
        StepMemory  $memory,
        StepHandler $handler,
    )
    {
        $data = $handler->$name;
        $alias = $name;
        foreach ($attrs as $attr) {
            $attr->init($name, $memory, $handler);

            $attr->beforeSave();
            if ($attr->preventDefault) return;

            $data = $attr->onSave($data);
            if ($attr->preventDefault) return;

            if (($alias0 = $attr->getAlias()) !== null) {
                $alias = $alias0;
            }
        }

        $memory->put($alias, $data);
    }

    /**
     * Load property data
     *
     * @param StepHandlerAttribute[] $attrs
     * @param string $name
     * @param StepMemory $memory
     * @param StepHandler $handler
     * @return void
     */
    public static function load(
        array       $attrs,
        string      $name,
        StepMemory  $memory,
        StepHandler $handler,
    )
    {
        $alias = $name;
        foreach ($attrs as $attr) {
            $attr->init($name, $memory, $handler);

            if (($alias0 = $attr->getAlias()) !== null) {
                $alias = $alias0;
            }
        }

        $data = $memory->get($alias);

        foreach ($attrs as $attr) {
            $attr->beforeLoad();
            if ($attr->preventDefault) return;

            $data = $attr->onLoad($data);
            if ($attr->preventDefault) return;
        }

        $handler->$name = $data;
    }

}