<?php

namespace Mmb\Action\Section\Resource;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;
use Mmb\Action\Form\Input;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\ResourceMaker;
use Mmb\Action\Section\Section;
use Mmb\Support\Caller\Caller;

class ResourceModule extends Section
{
    use Conditionable;

    public function __construct(
        public ResourceMaker $maker,
        public readonly string $name,
    )
    {
        parent::__construct($maker->section->context);

        // Back key
        $this->addBackKey(
            fn() => $this->getBackLabel(),
            fn() => $this->fireBack(),
            name: 'back',
            x   : 100,
        );
    }

    /**
     * Fire action
     *
     * @param            $action
     * @param array      $args
     * @param array      $dynamicArgs
     * @param array|null $openArgs
     * @return void
     */
    public function fireAction($action, array $args = [], array $dynamicArgs = [], array $openArgs = null)
    {
        if($action instanceof Closure)
        {
            $dynamicArgs += $this->getDynArgs();
            Caller::invoke($this->context, $action, $args, $dynamicArgs);
        }
        elseif(!$action)
        {
            $this->maker->section->open($this->maker->getDefault()->name, ...($openArgs ?? $args));
        }
        elseif(is_string($action))
        {
            $this->maker->section->open($action, ...($openArgs ?? $args));
        }
    }

    protected ?ResourceModule $linkedModule = null;

    public function linkTo(ResourceModule $module)
    {
        $this->linkedModule = $module;
        $this->bootLinked($module);
        $module->bootLinkedAnother($this);

        if(!isset($this->back))
        {
            $this->back = $module->name;
        }
    }

    public function module(ResourceModule $module)
    {
        $module->linkTo($this);
        $this->maker->addIfNotExists($module);
        return $this;
    }

    protected function bootLinked(ResourceModule $module)
    {
    }

    protected function bootLinkedAnother(ResourceModule $module)
    {
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
        $this->maker->section->set($name, $value);
        return $this;
    }

    /**
     * Get attribute
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->maker->section->get($name, $default);
    }

    /**
     * Set my attribute
     *
     * @param string $name
     * @param        $value
     * @return $this
     */
    public function setMy(string $name, $value)
    {
        return $this->set($this->name . ':' . $name, $value);
    }

    /**
     * Get my attribute
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function getMy(string $name, $default = null)
    {
        return $this->get($this->name . ':' . $name, $default);
    }

    public function forgotOwn()
    {
        $my = $this->name . ':';
        foreach($this->maker->section->attrs as $name => $attr)
        {
            if(str_starts_with($name, $my))
            {
                $this->set($name, null);
            }
        }
        return $this;
    }

    /**
     * Get value of
     *
     * @param $value
     * @param ...$args
     * @return mixed
     */
    protected function valueOf($value, ...$args)
    {
        if($value instanceof Closure)
        {
            $arr = [];
            $dyn = $this->getDynArgs() + [
                'module' => $this,
            ];
            foreach($args as $key => $v)
            {
                if(is_int($key))
                    $arr[] = $v;
                else
                    $dyn[$key] = $v;
            }

            return Caller::invoke($this->context, $value, $arr, $dyn);
        }

        return $value;
    }

    protected array $dynArgs = [];

    protected function getDynArgs()
    {
        return $this->dynArgs;
    }

    protected function getDynArgsOf(string ...$names)
    {
        return Arr::only($this->getDynArgs(), $names);
    }

    public function getDynArg(string $name, $default = null)
    {
        return $this->getDynArgs()[$name] ?? $this->valueOf($default);
    }

    public function setDynArg(string $name, $value)
    {
        $this->dynArgs[$name] = $value;
        return $this;
    }

    public function setDynArgs(...$args)
    {
        $this->dynArgs = array_replace($this->dynArgs, $args);
    }



    public function menu(string $__name, ...$__args)
    {
        return $this->maker->section->menu($__name, ...$__args);
    }

    public function inlineForm(string $__name, ...$__args)
    {
        return $this->maker->section->inlineForm($__name, ...$__args);
    }


    /**
     * Set this module to default
     *
     * @return $this
     */
    public function default()
    {
        $this->maker->default($this->name);
        return $this;
    }

    protected $back;
    protected $backLabel;

    public function back($action)
    {
        $this->back = $action;
        return $this;
    }

    public function backLabel($label)
    {
        $this->backLabel = $label;
        return $this;
    }

    protected function getBackAction()
    {
        return $this->back;
    }

    protected function getBackLabel()
    {
        return $this->backLabel ?? __('mmb::resource.default.back');
    }

    protected function fireBack(array $args = [], array $dynamicArgs = [])
    {
        $back = $this->getBackAction();

        if (!$back && $this->maker->getDefault() === $this)
        {
            $this->maker->section->back();
            return;
        }

        $this->fireAction($back, $args, $dynamicArgs);
    }

    public function onLeave()
    {

    }



    protected $keys = [
        'back'   => [],
    ];

    protected function addKey($label, string $at, $action, ?string $name, ?int $x, ?int $y, $condition = true)
    {
        $key = [
            'label'     => $label,
            'action'    => $action,
            'x'         => $x ?? PHP_INT_MAX,
            'y'         => $y ?? PHP_INT_MAX,
            'condition' => $condition,
        ];

        if(isset($name))
            $this->keys[$at][$name] = $key;
        else
            $this->keys[$at][] = $key;
    }

    protected function moveKey(string $name, string $at, int $x = null, int $y = null)
    {
        $k = &$this->keys[$at][$name];
        if(isset($x))
            $k['x'] = $x;
        if(isset($y))
            $k['y'] = $x;
    }

    public function removeKey(string $name, ?string $at = null)
    {
        if(isset($at))
        {
            unset($this->keys[$at][$name]);
        }
        else
        {
            foreach($this->keys as $n => $k)
            {
                unset($this->keys[$n][$name]);
            }
        }
    }

    protected function keyToSchema(Menu $menu, string $at, ...$args)
    {
        $r = [];
        foreach(collect($this->keys[$at])->sortBy('y')->groupBy('y') as $groupY)
        {
            $row = [];
            foreach($groupY->sortBy('x') as $key)
            {
                $action = $key['action'];
                $label = $this->valueOf($key['label'], ...$args);

                if(!$this->valueOf($key['condition'], ...$args))
                    continue;

                if($action === null)
                {
                    $row[] = $menu->key($label);
                }
                elseif(is_string($action))
                {
                    $row[] = $menu->key($label, fn() => $this->fireAction($action));
                }
                else
                {
                    $row[] = $menu->key($label, fn() => $this->valueOf($action));
                }
            }

            if($row)
                $r[] = $row;
        }

        return $r;
    }

    protected function keyToOptions(Input $input, string $at, ...$args)
    {
        $r = [];
        foreach(collect($this->keys[$at])->sortBy('y')->groupBy('y') as $groupY)
        {
            $row = [];
            foreach($groupY->sortBy('x') as $key)
            {
                $action = $key['action'];
                $label = $this->valueOf($key['label'], ...$args);

                if(!$this->valueOf($key['condition'], ...$args))
                    continue;

                if($action === null)
                {
                    $row[] = $input->keyAction($label, fn() => null);
                }
                elseif(is_string($action))
                {
                    $row[] = $input->keyAction($label, fn() => $this->fireAction($action));
                }
                else
                {
                    $row[] = $input->keyAction($label, $action);
                }
            }

            if($row)
                $r[] = $row;
        }

        return $r;
    }

    public function addBackKey(
        $label, $action = null, string $name = null, int $x = null, ?int $y = 0, $condition = true
    )
    {
        $this->addKey($label, 'back', $action, $name, $x, $y, $condition);
        return $this;
    }

    public function moveBackKey(string $name = 'back', int $x = null, int $y = null)
    {
        $this->moveKey($name, 'back', $x, $y);
        return $this;
    }

    /**
     * Use a model
     *
     * @param string $name
     * @return $this
     */
    public function use(string $name)
    {
        $this->setDynArg($name, fn() => $this->get($name));
        return $this;
    }

    protected function getInlineCallbackFor(InlineRegister $register)
    {
        try
        {
            return parent::getInlineCallbackFor($register);
        }
        catch (BadMethodCallException $e)
        {
            return null;
        }
    }

}
