<?php

namespace Mmb\Action\Section;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Support\Behavior\Behavior;
use Mmb\Support\Db\ModelFinder;

class ResourceSection extends Section
{

    protected $for;

    protected $with = [];

    /**
     * Initialize resource
     *
     * @param ResourceMaker $maker
     * @return void
     */
    public function resource(ResourceMaker $maker)
    {
    }

    private $_resource;

    /**
     * Get resource
     *
     * @return ResourceMaker
     */
    public function getResource()
    {
        if(!isset($this->_resource))
        {
            $maker = new ResourceMaker($this);
            $this->resource($maker);

            $this->_resource = $maker;
        }

        return $this->_resource;
    }

    /**
     * Get target model
     *
     * @return ?string
     */
    public function getFor()
    {
        return $this->for;
    }


    public array $attrs = [];

    /**
     * Get attribute
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return array_key_exists($name, $this->attrs) ? $this->attrs[$name] : value($default);
    }

    /**
     * Set attribute
     *
     * @param string $name
     * @param        $value
     * @return $this
     */
    public function set(string $name, $value)
    {
        if($value === null)
        {
            unset($this->attrs[$name]);
        }
        else
        {
            $this->attrs[$name] = $value;
        }
        return $this;
    }


    /**
     * Open module
     *
     * @param string $name
     * @param        ...$args
     * @return void
     */
    public function open(string $name, ...$args)
    {
        if($this->get('#') && $old = $this->getResource()->getModule($this->get('#')))
        {
            $old->onLeave();
        }

        $this->set('#', $name);
        $this->getResource()->getModule($name)->invoke('main', ...$args);
    }

    /**
     * Open default module
     *
     * @return void
     */
    public function main()
    {
        $this->open($this->getResource()->getDefault()->name);
    }

    /**
     * Get inline alias for a name
     *
     * @param InlineRegister $register
     * @return Closure|null
     */
    protected function getInlineCallbackFor(InlineRegister $register)
    {
        $register->inlineAction->with('attrs', ...$this->with);
        $module = $this->get('#') ? $this->getResource()->getModule($this->get('#')) : null;

        return $module?->getInlineCallbackFor($register) ??
                parent::getInlineCallbackFor($register);
    }

    /**
     * Back to the previous menu
     *
     * @return void
     */
    public function back()
    {
        Behavior::back($this->context, static::class);
    }

}
