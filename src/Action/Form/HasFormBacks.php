<?php

namespace Mmb\Action\Form;

use Mmb\Action\Action;
use Mmb\Action\Form\Attributes\AsAttribute;
use Mmb\Auth\AreaRegister;
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
        if ($back = $this->get('back') ?? app(AreaRegister::class)->getAttribute($this->_backUsingAreaClass ?? static::class, 'back'))
        {
            if (is_array($back) && is_string(@$back[0]) && is_a($back[0], Action::class, true))
            {
                $back[0] = new ($back[0])($this->update);
            }

            Caller::invoke($back, [], [
                'form' => $this,
                'finished' => $finished,
            ]);
        }
        else
        {
            $this->response("BACK IS NOT SET"); // TODO
        }
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
