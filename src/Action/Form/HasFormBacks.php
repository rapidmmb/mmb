<?php

namespace Mmb\Action\Form;

use Mmb\Action\Action;
use Mmb\Action\Form\Attributes\AsAttribute;
use Mmb\Auth\AreaRegister;
use Mmb\Support\Behavior\Behavior;
use Mmb\Support\Caller\Caller;

trait HasFormBacks
{

    // Add following lines to customize back:
    // #[AsAttribute]
    // public $back = ['CLASS', 'METHOD'];

    protected string $_backUsingAreaClass;

    public function back(bool $finished = true)
    {
        $this->fire('back', $finished);
    }

    public function onBack(bool $finished)
    {
        Behavior::back(dynamicArgs: [
            'form' => $this,
            'finished' => $finished,
        ]);
    }

    public function onCancel()
    {
        $this->back(false);
    }


    /**
     * Add back callback
     *
     * @param string|object|array $class
     * @param string|null         $method
     * @return $this
     */
    public function withBack(string|object|array $class, string $method = null)
    {
        if (!is_array($class))
        {
            $class = [is_object($class) ? get_class($class) : $class, $method];
        }

        $this->put('back', $class);

        return $this;
    }

    /**
     * Use a class for getting back from area settings
     *
     * @param string $baseClass
     * @return $this
     */
    public function withBackOfArea(string $baseClass)
    {
        $this->_backUsingAreaClass = $baseClass;
        return $this;
    }

}
