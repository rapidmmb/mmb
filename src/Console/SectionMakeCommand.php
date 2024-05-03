<?php

namespace Mmb\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class SectionMakeCommand extends BaseMmbMakeCommand
{

    protected $name = 'make:section';

    protected $description = 'Create new section';

    protected $type = 'Section';

    protected function getStub()
    {
        if($this->option('resource'))
        {
            return $this->resolveStub('/section-resource.stub');
        }

        return $this->resolveStub('/section.stub');
    }

    public function resolveStub(string $path)
    {
        return __DIR__ . '/stubs' . $path;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Mmb\\Sections';
    }

    protected function getOptions()
    {
        return [
            ['resource', 'r', InputOption::VALUE_REQUIRED, 'Indicates if the generated section should be a resource section'],
        ];
    }

    protected function getReplacements()
    {
        if($model = $this->option('resource'))
        {
            return [
                'model' => $model,
                'modelName' => Str::camel($model),
            ];
        }

        return [];
    }

}
