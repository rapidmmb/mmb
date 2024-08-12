<?php

namespace Mmb\Action\Form\Concerns;

use Illuminate\Support\Collection;
use Mmb\Action\Form\Attributes\FormDynamicPropertyAttributeContract;
use Mmb\Support\AttributeLoader\AttributeLoader;

trait InteractWithAttributes
{

    private array $attributes = [];

    /**
     * Get attribute
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if ($this->hasDynamicAttributes($name))
        {
            return $this->getDynamicAttribute($name);
        }

        return $this->has($name) ? $this->attributes[$name] : value($default);
    }

    /**
     * Get all attributes
     *
     * @return Collection
     */
    public function all()
    {
        return collect($this->attributes);
    }

    /**
     * Get attributes with specified name
     *
     * @param $keys
     * @return Collection
     */
    public function only($keys)
    {
        return $this->all()->only(...func_get_args());
    }

    /**
     * Get all attributes for inputs
     *
     * @return array
     */
    public function values()
    {
        return $this->only($this->inputs())->toArray();
    }

    /**
     * Merge attribute collection
     *
     * @param array ...$attrs
     * @return $this
     */
    public function merge(array ...$attrs)
    {
        $this->attributes = array_merge($this->attributes, ...$attrs);
        return $this;
    }

    /**
     * Set attribute value
     *
     * @param string $name
     * @param        $value
     * @return void
     */
    public function put(string $name, $value)
    {
        if ($this->hasDynamicAttributes($name))
        {
            $this->setDynamicAttribute($name, $value);
            return;
        }

        $this->attributes[$name] = $value;
    }

    /**
     * Checks have attribute
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->attributes) || $this->hasDynamicAttributes($name);
    }

    /**
     * Get attribute
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Set attribute
     *
     * @param string $name
     * @param        $value
     * @return void
     */
    public function __set(string $name, $value) : void
    {
        $this->put($name, $value);
    }

    /**
     * Get output for attributes
     *
     * @return array
     */
    protected function getOutAttributes()
    {
        return $this->getOutDynamicAttributes() + $this->attributes;
    }

    /**
     * Set input attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function setInAttributes(array $attributes)
    {
        $this->merge($attributes);
    }

    /**
     * Merge inputs
     *
     * @param array $attributes
     * @return $this
     */
    public function with(array $attributes)
    {
        $this->setInAttributes($attributes);
        return $this;
    }


    protected array $dynamicAttributes = [];

    /**
     * Merge dynamic attributes by name
     *
     * @param array $attributes
     * @return void
     */
    public function mergeDynamicAttributes(array $attributes)
    {
        array_push($this->dynamicAttributes, ...$attributes);
    }

    /**
     * Get dynamic attributes that initialized in the class properties.
     *
     * @return array
     */
    public function getDynamicAttributeNames()
    {
        return $this->dynamicAttributes;
    }

    /**
     * Determines that form has a dynamic attribute
     *
     * @param string $name
     * @return bool
     */
    public function hasDynamicAttributes(string $name)
    {
        return in_array($name, $this->getDynamicAttributeNames());
    }

    /**
     * Get a dynamic attribute
     *
     * @param string $name
     * @return mixed
     */
    public function getDynamicAttribute(string $name)
    {
        return $this->{$name} ?? null;
    }

    /**
     * Set a dynamic attribute
     *
     * @param string $name
     * @param        $value
     * @param bool   $casting
     * @return void
     */
    public function setDynamicAttribute(string $name, $value, bool $casting = false)
    {
        if ($casting)
        {
            foreach (AttributeLoader::getPropertyAttributesOf($this, $name, FormDynamicPropertyAttributeContract::class) as $attr)
            {
                $value = $attr->getFormDynamicPropertyForLoad($this, $name, $value);
            }
        }

        $this->{$name} = $value;
    }

    /**
     * Load dynamic attribute from inputs
     *
     * @return void
     */
    protected function loadDynamicAttributesFromIn()
    {
        foreach ($this->getDynamicAttributeNames() as $name)
        {
            if (array_key_exists($name, $this->attributes))
            {
                $this->setDynamicAttribute($name, $this->attributes[$name], true);
                unset($this->attributes[$name]);
            }
        }
    }

    /**
     * Get output for dynamic attributes
     *
     * @return array
     */
    protected function getOutDynamicAttributes() : array
    {
        $data = [];

        foreach ($this->getDynamicAttributeNames() as $name)
        {
            $value = $this->getDynamicAttribute($name);

            foreach (AttributeLoader::getPropertyAttributesOf($this, $name, FormDynamicPropertyAttributeContract::class) as $attr)
            {
                $value = $attr->getFormDynamicPropertyForStore($this, $name, $value);
            }

            $data[$name] = $value;
        }

        return $data;
    }

}
