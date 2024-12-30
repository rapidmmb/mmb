<?php

namespace Mmb\Action\Section;

use Mmb\Action\Section\Resource\ResourceInfoModule;
use Mmb\Action\Section\Resource\ResourceListModule;
use Mmb\Action\Section\Resource\ResourceModule;

class ResourceMaker
{

    public function __construct(
        public ResourceSection $section,
    )
    {
    }

    protected array $inputs = [];

    /**
     * Add normal input
     *
     * @param string $name
     * @return void
     */
    public function input(string $name)
    {
        $this->inputs[$name] = '';
    }

    /**
     * Add model input
     *
     * @param string      $model
     * @param string|null $name
     * @return void
     */
    public function inputModel(string $model, string $name = null)
    {
        $this->inputs[$name ?? class_basename($model)] = $model;
    }


    protected string $default;

    /**
     * Set default module
     *
     * @param string $name
     * @return $this
     */
    public function default(string $name)
    {
        $this->default = $name;
        return $this;
    }

    /**
     * Get default module name
     *
     * @return ResourceModule
     */
    public function getDefault()
    {
        return $this->modules[$this->default ?? array_key_first($this->modules)] ?? null;
    }


    protected array $modules = [];

    /**
     * Add module
     *
     * @param ResourceModule $module
     * @return $this
     */
    public function module(Resource\ResourceModule $module)
    {
        if(isset($this->modules[$module->name]))
        {
            throw new \InvalidArgumentException("Duplicated module named [$module->name]");
        }
        $module->context = $this->section->context;
        $this->modules[$module->name] = $module;
        return $this;
    }

    /**
     * Add module if not exists
     *
     * @param ResourceModule $module
     * @return $this
     */
    public function addIfNotExists(Resource\ResourceModule $module)
    {
        if(!in_array($module, $this->modules))
        {
            $this->module($module);
        }

        return $this;
    }

    /**
     * Create list module
     *
     * @param string $model
     * @param string $name
     * @return ResourceListModule
     */
    public function listFor(string $model, string $name = 'list')
    {
        $this->module($module = new ResourceListModule($this, $name, $model));
        return $module;
    }

    /**
     * Create list module
     *
     * @param string $name
     * @return ResourceListModule
     */
    public function list(string $name = 'list')
    {
        return $this->listFor($this->section->getFor(), $name);
    }

    /**
     * Create info module
     *
     * @param string $model
     * @param string $name
     * @return ResourceInfoModule
     */
    public function infoFor(string $model, string $name = 'info')
    {
        $this->module($module = new ResourceInfoModule($this, $name, $model));
        return $module;
    }

    /**
     * Create info module
     *
     * @param string $name
     * @return ResourceInfoModule
     */
    public function info(string $name = 'info')
    {
        return $this->infoFor($this->section->getFor(), $name);
    }


    /**
     * Get module
     *
     * @param string $name
     * @param        $default
     * @return ResourceModule
     */
    public function getModule(string $name, $default = null)
    {
        return $this->modules[$name] ?? value($default);
    }

    /**
     * Get module or fail
     *
     * @param string $name
     * @return ResourceModule
     */
    public function getModuleOrFail(string $name)
    {
        return $this->getModule($name, fn() => denied(404));
    }

}
