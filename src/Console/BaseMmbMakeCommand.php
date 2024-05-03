<?php

namespace Mmb\Console;

use Illuminate\Console\GeneratorCommand;

abstract class BaseMmbMakeCommand extends GeneratorCommand
{

    protected function getReplacements()
    {
        return [];
    }

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        foreach($this->getReplacements() as $from => $to)
        {
            $class = preg_replace('/\{\{\s*'.$from.'\s*\}\}/i', $to, $class);
        }

        return $class;
    }

}
