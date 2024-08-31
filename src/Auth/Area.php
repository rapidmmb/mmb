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


    protected string $class;
    protected string $namespace;


    /**
     * Add class authorize
     *
     * @param string       $class
     * @param string|array $ability
     * @return void
     */
    protected final function authClass(string $class, string|array $ability)
    {
        if (isset($this->namespace))
        {
            $class = trim($this->namespace, '\\') . '\\' . trim($class, '\\');
        }

        app(AreaRegister::class)->authClass($class, $ability);
    }

    /**
     * Add namespace authorize
     *
     * @param string       $namespace
     * @param string|array $ability
     * @return void
     */
    protected final function authNamespace(string $namespace, string|array $ability)
    {
        if (isset($this->namespace))
        {
            $namespace = trim($this->namespace, '\\') . '\\' . trim($namespace, '\\');
        }

        app(AreaRegister::class)->authNamespace($namespace, $ability);
    }

    /**
     * Add authorize using $class or $namespace property
     *
     * @param string|array $ability
     * @return void
     */
    protected final function auth(string|array $ability)
    {
        if (isset($this->class))
        {
            app(AreaRegister::class)->authClass($this->class, $ability);
        }
        else
        {
            app(AreaRegister::class)->authNamespace($this->namespace, $ability);
        }
    }


    /**
     * Set class attribute
     *
     * @param string $class
     * @param string $attribute
     * @param        $value
     * @return void
     */
    protected final function putClass(string $class, string $attribute, $value)
    {
        if (isset($this->namespace))
        {
            $class = trim($this->namespace, '\\') . '\\' . trim($class, '\\');
        }

        app(AreaRegister::class)->putForClass($class, $attribute, $value);
    }

    /**
     * Set namespace attribute
     *
     * @param string $namespace
     * @param string $attribute
     * @param        $value
     * @return void
     */
    protected final function putNamespace(string $namespace, string $attribute, $value)
    {
        if (isset($this->namespace))
        {
            $namespace = trim($this->namespace, '\\') . '\\' . trim($namespace, '\\');
        }

        app(AreaRegister::class)->putForNamespace($namespace, $attribute, $value);
    }

    /**
     * Set attribute using $class or $namespace property
     *
     * @param string $attribute
     * @param        $value
     * @return void
     */
    protected final function put(string $attribute, $value)
    {
        if (isset($this->class))
        {
            app(AreaRegister::class)->putForClass($this->class, $attribute, $value);
        }
        else
        {
            app(AreaRegister::class)->putForNamespace($this->namespace, $attribute, $value);
        }
    }

    /**
     * Set back attribute using $class or $namespace
     *
     * @param string $class
     * @param string $method
     * @return void
     */
    protected function backUsing(string $class, string $method)
    {
        $this->put('back', [$class, $method]);
    }

    /**
     * Set back attribute for a class
     *
     * @param string $class
     * @param string $backClass
     * @param string $backMethod
     * @return void
     */
    protected function backUsingForClass(string $class, string $backClass, string $backMethod)
    {
        if (isset($this->namespace))
        {
            $class = trim($this->namespace, '\\') . '\\' . trim($class, '\\');
        }

        app(AreaRegister::class)->putForClass($class, 'back', [$backMethod, $backClass]);
    }

    /**
     * Set back attribute for a namespace
     *
     * @param string $namespace
     * @param string $backClass
     * @param string $backMethod
     * @return void
     */
    protected function backUsingForNamespace(string $namespace, string $backClass, string $backMethod)
    {
        if (isset($this->namespace))
        {
            $namespace = trim($this->namespace, '\\') . '\\' . trim($namespace, '\\');
        }

        app(AreaRegister::class)->putForNamespace($namespace, 'back', [$backMethod, $backClass]);
    }

    /**
     * Set back system using $class or $namespace
     *
     * @param string $class
     * @param string $method
     * @return void
     */
    protected function backSystem(string $class, string $method)
    {
        $this->put('back-system', [$class, $method]);
    }

    /**
     * Set back system for a class
     *
     * @param string $class
     * @param string $backClass
     * @param string $backMethod
     * @return void
     */
    protected function backSystemForClass(string $class, string $backClass, string $backMethod)
    {
        if (isset($this->namespace))
        {
            $class = trim($this->namespace, '\\') . '\\' . trim($class, '\\');
        }

        app(AreaRegister::class)->putForClass($class, 'back-system', [$backMethod, $backClass]);
    }

    /**
     * Set back system for a namespace
     *
     * @param string $namespace
     * @param string $backClass
     * @param string $backMethod
     * @return void
     */
    protected function backSystemForNamespace(string $namespace, string $backClass, string $backMethod)
    {
        if (isset($this->namespace))
        {
            $namespace = trim($this->namespace, '\\') . '\\' . trim($namespace, '\\');
        }

        app(AreaRegister::class)->putForNamespace($namespace, 'back-system', [$backMethod, $backClass]);
    }

}
