<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerWriteResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;

interface SearchOwnerSource
{
    public function append(SearchOwnerRevisionState $revision): SearchOwnerWriteResult;

    public function read(SearchDocumentId $documentId, SearchObservedAt $observedAt): SearchOwnerReadResult;

    /** @return list<SearchOwnerRevisionState> */
    public function history(SearchDocumentId $documentId): array;
}
