<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract\SearchOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract\SearchOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicSearchOwnerSourceRuntimeReadV1 implements SearchOwnerSourceRuntimeReadV1
{
    private const PROBE_DOCUMENT_ID = '00000000-0000-4000-8000-000000005502';

    private const RUNTIME_READ_ID = 'search-discovery.owner-source-runtime-read';

    private const VERSION = 'search-owner-source-runtime-read-v1';

    public function __construct(
        private SearchOwnerSource $source,
        private SearchOwnerSourceRuntimeReadPolicy $policy,
    ) {}

    public function read(SearchDocumentId $documentId, SearchObservedAt $observedAt): SearchOwnerSourceRuntimeReadResult
    {
        try {
            return $this->policy->reduce($this->source->read($documentId, $observedAt));
        } catch (Throwable) {
            return SearchOwnerSourceRuntimeReadResult::dependencyUnavailable();
        }
    }

    public function diagnostics(): SearchOwnerSourceRuntimeReadDiagnostics
    {
        return new SearchOwnerSourceRuntimeReadDiagnostics(self::RUNTIME_READ_ID, self::VERSION, $this->availability());
    }

    private function availability(): SearchOwnerSourceRuntimeReadAvailability
    {
        try {
            $result = $this->source->read(
                SearchDocumentId::fromString(self::PROBE_DOCUMENT_ID),
                new SearchObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                SearchOwnerReadStatus::Found,
                SearchOwnerReadStatus::Missing => SearchOwnerSourceRuntimeReadAvailability::Available,
                SearchOwnerReadStatus::Corrupted => SearchOwnerSourceRuntimeReadAvailability::Corrupted,
                SearchOwnerReadStatus::DependencyUnavailable => SearchOwnerSourceRuntimeReadAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return SearchOwnerSourceRuntimeReadAvailability::DependencyUnavailable;
        }
    }
}
