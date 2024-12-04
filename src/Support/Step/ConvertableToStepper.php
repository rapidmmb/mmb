<?php

namespace Mmb\Support\Step;

interface ConvertableToStepper
{

    public function toStepper(): Stepper;

}