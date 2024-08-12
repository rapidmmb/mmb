<?php

namespace Mmb\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class AreaMakeCommand extends BaseMmbMakeCommand
{

    protected $name = 'make:area';

    protected $description = 'Create new area';

    protected $type = 'Area';

    protected function getStub()
    {
        return $this->resolveStub('/area.stub');
    }

    public function resolveStub(string $path)
    {
        return __DIR__ . '/stubs' . $path;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Mmb\\Areas';
    }

}
