<?php

namespace Mmb\Action\Road\Station\Concerns;

use Illuminate\Support\Str;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\FormKey;
use Mmb\Action\Form\Inline\InlineForm;
use Mmb\Action\Form\Input;
use Mmb\Action\Road\Customize\FormCustomizer;
use Closure;
use Mmb\Action\Road\Customize\InputCustomizer;

trait SignWithFormCustomizing
{

    protected function bootHasFormCustomizing()
    {
        match ($this->road->getRtl())
        {
            true    => $this->rtl(),
            false   => $this->ltr(),
            default => null,
        };
    }

    private FormCustomizer $_formCustomizer;

    public function getFormCustomizer() : FormCustomizer
    {
        return $this->_formCustomizer ??= new FormCustomizer($this);
    }

    /**
     * Get form customizer in callback
     *
     * @param Closure $callback
     * @return $this
     */
    public function useForm(Closure $callback)
    {
        $callback($this->getFormCustomizer());
        return $this;
    }

    /**
     * Insert new input
     *
     * @param string                                $name
     * @param Closure(Input $input): void           $callback
     * @param string|null                           $chunk
     * @param int                                   $order
     * @param (Closure(InputCustomizer): void)|null $customize
     * @return $this
     */
    public function insertInput(
        string   $name,
        Closure  $callback,
        ?string  $chunk = null,
        int      $order = 50,
        ?Closure $customize = null
    )
    {
        $input = $this->getFormCustomizer()->insertInput($name, $callback, $chunk, $order);

        if ($customize)
        {
            $customize($input);
        }

        return $this;
    }

    public function useInput(string $name, Closure $callback)
    {
        if ($input = $this->getFormCustomizer()->getInput($name))
        {
            $callback($input);

            return $this;
        }

        throw new \InvalidArgumentException("Input [$name] is not exists");
    }

    /**
     * Insert a key
     *
     * @param string                  $group
     * @param Closure(Form): ?FormKey $key
     * @param string|null             $name
     * @param int                     $x
     * @param int                     $y
     * @return $this
     */
    public function insertKey(string $group, Closure $key, ?string $name = null, int $x = 100, int $y = PHP_INT_MAX)
    {
        $this->getFormCustomizer()->insertKey($group, $key, $name, $x, $y);
        return $this;
    }

    /**
     * Insert a key row
     *
     * @param string                         $group
     * @param Closure(Form): array<?FormKey> $key
     * @param string|null                    $name
     * @param int                            $y
     * @param bool|null                      $rtl
     * @return $this
     */
    public function insertRow(string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null
    )
    {
        $this->getFormCustomizer()->insertRow($group, $key, $name, $y, $rtl);
        return $this;
    }

    /**
     * Insert a key schema
     *
     * @param string                                $group
     * @param Closure(Form): array<array<?FormKey>> $key
     * @param string|null                           $name
     * @param int                                   $y
     * @param bool|null                             $rtl
     * @return $this
     */
    public function insertSchema(
        string $group, Closure $key, ?string $name = null, int $y = PHP_INT_MAX, ?bool $rtl = null
    )
    {
        $this->getFormCustomizer()->insertSchema($group, $key, $name, $y, $rtl);
        return $this;
    }

    /**
     * Remove key by name
     *
     * @param string $group
     * @param string $name
     * @return $this
     */
    public function removeKey(string $group, string $name)
    {
        $this->getFormCustomizer()->removeKey($group, $name);
        return $this;
    }

    /**
     * Move key by name
     *
     * @param string   $group
     * @param string   $name
     * @param int|null $x
     * @param int|null $y
     * @return $this
     */
    public function moveKey(string $group, string $name, ?int $x, ?int $y)
    {
        $this->getFormCustomizer()->moveKey($group, $name, $x, $y);
        return $this;
    }

    /**
     * Set rtl the keyboard
     *
     * @return $this
     */
    public function rtl()
    {
        $this->getFormCustomizer()->rtl();
        return $this;
    }

    /**
     * Set ltr the keyboard
     *
     * @return $this
     */
    public function ltr()
    {
        $this->getFormCustomizer()->ltr();
        return $this;
    }

    /**
     * Call a key manager method (use this method in the magic __call)
     *
     * @param string $method
     * @param array  $args
     * @return bool
     */
    protected function callKeyManager(string $method, array $args) : bool
    {
        if (str_starts_with($method, 'insert'))
        {
            $type = match (true)
            {
                str_ends_with($method, 'Key')    => 'Key',
                str_ends_with($method, 'Row')    => 'Row',
                str_ends_with($method, 'Schema') => 'Schema',
            };

            if ($type)
            {
                $this->{'insert' . $type}(Str::kebab(substr($method, 6, -strlen($type))), ...$args);
                return true;
            }
        }

        if (str_starts_with($method, 'remove') && str_ends_with($method, 'Key'))
        {
            $this->removeKey(Str::kebab(substr($method, 6, -3)), ...$args);
            return true;
        }

        if (str_starts_with($method, 'move') && str_ends_with($method, 'Key'))
        {
            $this->moveKey(Str::kebab(substr($method, 4, -3)), ...$args);
            return true;
        }

        return false;
    }

    public function __call(string $name, array $arguments)
    {
        if ($this->callKeyManager($name, $arguments))
        {
            return $this;
        }

        return parent::__call($name, $arguments);
    }

}