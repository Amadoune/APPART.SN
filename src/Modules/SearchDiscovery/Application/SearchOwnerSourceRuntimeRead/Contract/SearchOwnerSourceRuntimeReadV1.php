<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;

interface SearchOwnerSourceRuntimeReadV1
{
    public function read(SearchDocumentId $documentId, SearchObservedAt $observedAt): SearchOwnerSourceRuntimeReadResult;

    public function diagnostics(): SearchOwnerSourceRuntimeReadDiagnostics;
}
