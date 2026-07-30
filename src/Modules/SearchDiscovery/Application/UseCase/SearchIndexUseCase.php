<?php

namespace Appart\Modules\SearchDiscovery\Application\UseCase;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchIndexRegistry;
use Appart\Modules\SearchDiscovery\Domain\Exception\SearchIndexNotFound;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchIndex;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;

abstract readonly class SearchIndexUseCase
{
    public function __construct(protected SearchIndexRegistry $indexes) {}

    /** @return array{SearchIndex,int} */
    protected function load(SearchIndexId $id): array
    {
        $index = $this->indexes->find($id) ?? throw new SearchIndexNotFound;

        return [$index, $index->version()];
    }
}
