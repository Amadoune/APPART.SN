<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;

final readonly class SearchQueryResolutionReadResult
{
    private function __construct(
        public string $queryFingerprint,
        public SearchQueryResolutionReadStatus $status,
        public ?SearchQueryResolutionRevisionState $revision,
    ) {}

    public static function found(SearchQueryResolutionRevisionState $revision): self
    {
        $status = $revision->decision === SearchQueryResolutionRevisionDecision::Found
            ? SearchQueryResolutionReadStatus::Found
            : SearchQueryResolutionReadStatus::Empty;

        return new self($revision->queryFingerprint, $status, $revision);
    }

    public static function missing(SearchQuery $query): self
    {
        return new self(SearchQueryResolutionRevisionState::fingerprint($query), SearchQueryResolutionReadStatus::Missing, null);
    }

    public static function corrupted(SearchQuery $query): self
    {
        return new self(SearchQueryResolutionRevisionState::fingerprint($query), SearchQueryResolutionReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(SearchQuery $query): self
    {
        return new self(SearchQueryResolutionRevisionState::fingerprint($query), SearchQueryResolutionReadStatus::DependencyUnavailable, null);
    }
}
