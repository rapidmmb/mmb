<?php

namespace Mmb\Support\Behavior;

use Mmb\Auth\AreaRegister;
use Mmb\Context;
use Mmb\Support\Behavior\Contracts\BackSystem;
use Mmb\Support\Behavior\Systems\FixedBackSystem;

class BehaviorFactory
{

    public function __construct()
    {
        $this->defaultBackSystem = new FixedBackSystem();
    }


    protected ?string $currentClass;

    public function getCurrentClass(): ?string
    {
        return $this->currentClass;
    }


    protected BackSystem $defaultBackSystem;

    public function setDefaultBackSystem(BackSystem $system)
    {
        $this->defaultBackSystem = $system;
    }

    public function back(Context $context, string $class = null, array $args = [], array $dynamicArgs = [])
    {
        $this->currentClass = $class;

        try {

            if (isset($class)) {
                if ($system = app(AreaRegister::class)->getAttribute($class, 'back-system')) {
                    if ($system instanceof BackSystem) {

                        $system->back($context, $args, $dynamicArgs);
                        return;

                    } else {

                        throw new \TypeError(
                            sprintf(
                                "Back system should be type of [%s], given [%s]",
                                BackSystem::class,
                                smartTypeOf($system)
                            )
                        );

                    }
                }
            }

            $this->defaultBackSystem->back($context, $args, $dynamicArgs);

        } finally {

            $this->currentClass = null;

        }
    }

}