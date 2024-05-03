<?php

namespace Mmb\Action\Memory;

interface ConvertableToStep
{

    public function toStep() : ?StepHandler;

}
