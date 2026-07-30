<?php

namespace Tests\Unit\Application\Support;

use App\Application\Contract\PublicListingQuery;
use App\ReadModels\PublicListingReadModel;
use LogicException;

final class InMemoryPublicListingQuery implements PublicListingQuery
{
    /** @var array<string, PublicListingReadModel> */
    private array $models = [];

    /** @param list<PublicListingReadModel> $models */
    public function __construct(array $models)
    {
        foreach ($models as $model) {
            $urlPath = parse_url($model->canonicalUrl, PHP_URL_PATH);
            $path = is_string($urlPath) ? ltrim($urlPath, '/') : null;
            if (! is_string($path) || $path === '' || isset($this->models[$path])) {
                throw new LogicException('Public listing canonical paths must be unique and valid.');
            }

            $this->models[$path] = $model;
        }
    }

    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
    {
        $model = $this->models[$canonicalPath] ?? null;

        return $model === null ? null : clone $model;
    }
}
