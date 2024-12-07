<?php

namespace Mmb\Support\Step\Contracts;

interface ConvertableToStepper
{

    public function toStepper(): Stepper;

}