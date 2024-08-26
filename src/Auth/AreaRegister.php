<?php

namespace Mmb\Auth;

use Illuminate\Contracts\Auth\Access\Gate;
use Mmb\Mmb;

class AreaRegister
{

    private array $classes = [];

    /**
     * Add class authorize
     *
     * @param string               $class
     * @param string|array $ability
     * @return void
     */
    public function authClass(string $class, string|array $ability)
    {
        @$this->classes[$class][] = $ability;
    }


    private array $namespaces = [];

    /**
     * Add namespace authorize
     *
     * @param string               $namespace
     * @param string|array $ability
     * @return void
     */
    public function authNamespace(string $namespace, string|array $ability)
    {
        if(!str_ends_with($namespace, '\\'))
        {
            $namespace .= '\\';
        }

        @$this->namespaces[$namespace][] = $ability;
    }

    private array $attributes = [];

    private array $attribute_namespaces = [];

    public function putForClass(string $class, string $attribute, $value)
    {
        $this->attributes[$class][$attribute] = $value;
    }

    public function putForNamespace(string $namespace, string $attribute, $value)
    {
        if(!str_ends_with($namespace, '\\'))
        {
            $namespace .= '\\';
        }

        $this->attribute_namespaces[$namespace][$attribute] = $value;
    }


    protected $authorizeCache = [];

    /**
     * Authorize class
     *
     * @param string $class
     * @return void
     */
    public function authorize(string $class)
    {
        if(isset($this->authorizeCache[$class]))
        {
            if($this->authorizeCache[$class])
            {
                throw $this->authorizeCache[$class];
            }
            else
            {
                return;
            }
        }

        try
        {
            $guard = app(Gate::class)->forUser(Mmb::guard()->user());

            if(isset($this->classes[$class]))
            {
                foreach($this->classes[$class] as $ability)
                {
                    $guard->authorize($ability);
                }
            }

            foreach($this->namespaces as $namespace => $abilities)
            {
                if(str_starts_with($class, $namespace))
                {
                    foreach($abilities as $ability)
                    {
                        $guard->authorize($ability);
                    }
                }
            }

            $this->authorizeCache[$class] = false;
        }
        catch(\Exception $e)
        {
            $this->authorizeCache[$class] = $e;
            throw $e;
        }
    }

    protected $canCache = [];

    /**
     * Check abilities
     *
     * @param string $class
     * @return bool
     */
    public function can(string $class)
    {
        if(isset($this->canCache[$class]))
        {
            return $this->canCache[$class];
        }

        $guard = app(Gate::class)->forUser(Mmb::guard()->user());

        if(isset($this->classes[$class]))
        {
            foreach($this->classes[$class] as $ability)
            {
                if(!$guard->allows($ability))
                {
                    return $this->canCache[$class] = false;
                }
            }
        }

        foreach($this->namespaces as $namespace => $abilities)
        {
            if(str_starts_with($class, $namespace))
            {
                foreach($abilities as $ability)
                {
                    if(!$guard->allows($ability))
                    {
                        return $this->canCache[$class] = false;
                    }
                }
            }
        }

        return $this->canCache[$class] = true;
    }

    /**
     * Get area attribute
     *
     * @param string $class
     * @param string $attribute
     * @param mixed  $default
     * @return mixed
     */
    public function getAttribute(string $class, string $attribute, $default = null)
    {
        if (array_key_exists($class, $this->attributes) && array_key_exists($attribute, $this->attributes[$class]))
        {
            return value($this->attributes[$class][$attribute]);
        }

        $bestLength = 0;
        $result = $default;

        foreach ($this->attribute_namespaces as $namespace => $value)
        {
            if (str_starts_with($class, $namespace) && array_key_exists($attribute, $value) && strlen($namespace) >= $bestLength)
            {
                $bestLength = strlen($namespace);
                $result = $value;
            }
        }

        return $result === null ? null : value($result[$attribute]);
    }

}
