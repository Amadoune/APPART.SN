<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\Contract\SearchOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract\SearchOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy implements SearchOwnerSourceRuntimeAvailabilityPolicy
{
    private const PROBE_DOCUMENT_ID = '00000000-0000-4000-8000-000000005501';

    public function __construct(private SearchOwnerSource $source) {}

    public function inspect(): SearchOwnerSourceRuntimeAvailability
    {
        try {
            $result = $this->source->read(
                SearchDocumentId::fromString(self::PROBE_DOCUMENT_ID),
                new SearchObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                SearchOwnerReadStatus::Found,
                SearchOwnerReadStatus::Missing => SearchOwnerSourceRuntimeAvailability::Available,
                SearchOwnerReadStatus::Corrupted => SearchOwnerSourceRuntimeAvailability::Corrupted,
                SearchOwnerReadStatus::DependencyUnavailable => SearchOwnerSourceRuntimeAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return SearchOwnerSourceRuntimeAvailability::DependencyUnavailable;
        }
    }
}
