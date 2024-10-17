<?php

namespace Mmb\Support\Db\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class FindByRepo extends Find
{

    public function __construct(
        public ?string $repo = null,
        public ?int $error = 404,
    )
    {
        parent::__construct(null, $this->error);
    }

    protected function getRepo()
    {
        return app($this->repo ?? str_replace('\\Models\\', '\\Repositories\\', $this->classType) . 'Repository');
    }

    protected function getUsableValue($value)
    {
        $repo = $this->getRepo();

        // todo: should change

        if (method_exists($repo, 'findOr'))
        {
            return $repo->findOr($value, isset($this->error) ? fn () => abort($this->error) : null);
        }
        elseif (method_exists($repo, 'findById'))
        {
            return $repo->findById($value) ?? (isset($this->error) ? abort($this->error) : null);
        }
        elseif (method_exists($repo, 'find'))
        {
            return $repo->find($value) ?? (isset($this->error) ? abort($this->error) : null);
        }

        throw new \BadMethodCallException(sprintf("FindByRepo required [findOr] or [findById] or [find] method in [%s]", get_class($repo)));
    }

}