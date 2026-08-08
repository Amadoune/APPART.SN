<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\Contract\SearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy implements SearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy
{
    private const PROBE_QUERY = 'runtime-health-probe';

    public function __construct(private SearchQueryResolutionOwnerSource $source) {}

    public function inspect(): SearchQueryResolutionOwnerSourceRuntimeAvailability
    {
        try {
            $result = $this->source->read(
                new SearchQuery(self::PROBE_QUERY),
                new SearchQueryResolutionObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                SearchQueryResolutionReadStatus::Found,
                SearchQueryResolutionReadStatus::Empty,
                SearchQueryResolutionReadStatus::Missing => SearchQueryResolutionOwnerSourceRuntimeAvailability::Available,
                SearchQueryResolutionReadStatus::Corrupted => SearchQueryResolutionOwnerSourceRuntimeAvailability::Corrupted,
                SearchQueryResolutionReadStatus::DependencyUnavailable => SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable;
        }
    }
}
