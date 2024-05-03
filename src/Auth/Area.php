<?php

namespace Mmb\Auth;

use Closure;

class Area
{

    /**
     * Boot area
     *
     * @return void
     */
    public function boot()
    {
    }


    /**
     * Add class authorize
     *
     * @param string       $class
     * @param string|array $ability
     * @return void
     */
    protected final function forClass(string $class, string|array $ability)
    {
        app(AreaRegister::class)->forClass($class, $ability);
    }

    /**
     * Add namespace authorize
     *
     * @param string       $namespace
     * @param string|array $ability
     * @return void
     */
    protected final function forNamespace(string $namespace, string|array $ability)
    {
        app(AreaRegister::class)->forNamespace($namespace, $ability);
    }

}
