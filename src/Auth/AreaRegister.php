<?php

namespace Mmb\Auth;

use Illuminate\Contracts\Auth\Access\Gate;

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
    public function forClass(string $class, string|array $ability)
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
    public function forNamespace(string $namespace, string|array $ability)
    {
        if(!str_ends_with($namespace, '\\'))
        {
            $namespace .= '\\';
        }

        @$this->namespaces[$namespace][] = $ability;
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
            $guard = app(Gate::class)->forUser(auth()->user());

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

        $guard = app(Gate::class)->forUser(auth()->user());

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

}
