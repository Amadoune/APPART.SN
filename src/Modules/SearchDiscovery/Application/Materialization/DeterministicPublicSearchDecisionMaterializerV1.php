<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadStatus;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;
use Throwable;

final readonly class DeterministicPublicSearchDecisionMaterializerV1 implements MaterializePublicSearchDecisionV1
{
    public function __construct(
        private PublicSearchMaterializationSourceReaderV1 $sources,
        private PublicSearchRankingPolicyV1 $ranking,
        private SearchProjectionPolicy $projections,
        private PublicSearchDecisionIdentityV1 $identities,
        private SearchDecisionReader $reader,
        private SearchDecisionWriter $writer,
    ) {}

    public function materialize(ListingId $listingId): PublicSearchMaterializationResult
    {
        try {
            $read = $this->sources->read($listingId);
            if ($read->status !== PublicSearchMaterializationSourceStatus::Found || $read->sources === null) {
                return PublicSearchMaterializationResult::of(match ($read->status) {
                    PublicSearchMaterializationSourceStatus::Missing => PublicSearchMaterializationStatus::SourceMissing,
                    PublicSearchMaterializationSourceStatus::Corrupted => PublicSearchMaterializationStatus::SourceCorrupted,
                    PublicSearchMaterializationSourceStatus::DependencyUnavailable => PublicSearchMaterializationStatus::DependencyUnavailable,
                    PublicSearchMaterializationSourceStatus::Found => PublicSearchMaterializationStatus::SourceCorrupted,
                });
            }

            $ranking = $this->ranking->decide();
            $sources = $read->sources;
            $projection = $this->projections->build(
                new ListingProjectionSource($listingId, $sources->listingState, $ranking->rank, [], $sources->listingRevision),
                new PropertyProjectionSource($listingId, $sources->propertyState, [], $sources->propertyRevision),
                new MediaProjectionSource($listingId, $sources->mediaState, [], $sources->mediaRevision),
            );
            if ($projection->facets !== $ranking->facets) {
                return PublicSearchMaterializationResult::of(PublicSearchMaterializationStatus::SourceCorrupted);
            }

            $decisionId = $this->identities->issue($listingId, $ranking->policyId);
            $current = $this->reader->readByListing($listingId);
            if ($current->status === SearchDecisionReadStatus::Corrupted) {
                return PublicSearchMaterializationResult::of(PublicSearchMaterializationStatus::SourceCorrupted);
            }

            $version = 1;
            if ($current->status === SearchDecisionReadStatus::Found && $current->decision !== null) {
                $relation = $this->relation($projection->revisions, $current->decision->projection->revisions);
                if ($relation === 'older') {
                    return PublicSearchMaterializationResult::of(PublicSearchMaterializationStatus::RejectedObsolete, $current->decision);
                }
                if ($relation === 'divergent') {
                    return PublicSearchMaterializationResult::of(PublicSearchMaterializationStatus::Divergent, $current->decision);
                }
                $version = $relation === 'same' ? $current->decision->version : $current->decision->version + 1;
            }

            $decision = new SearchDecision($decisionId, $listingId, $version, $projection);

            return PublicSearchMaterializationResult::of($this->status($this->writer->store($decision)), $decision);
        } catch (Throwable) {
            return PublicSearchMaterializationResult::of(PublicSearchMaterializationStatus::DependencyUnavailable);
        }
    }

    private function relation(SourceRevisionSet $candidate, SourceRevisionSet $current): string
    {
        $greater = false;
        $lower = false;
        foreach (SourceKind::cases() as $source) {
            $next = $candidate->for($source);
            $stored = $current->for($source);
            if ($next->version === $stored->version && ! $next->sameFact($stored)) {
                return 'divergent';
            }
            $greater = $greater || $next->version > $stored->version;
            $lower = $lower || $next->version < $stored->version;
        }
        if ($greater && $lower) {
            return 'divergent';
        }

        return $greater ? 'newer' : ($lower ? 'older' : 'same');
    }

    private function status(SearchDecisionWriteResult $result): PublicSearchMaterializationStatus
    {
        return match ($result) {
            SearchDecisionWriteResult::Applied => PublicSearchMaterializationStatus::Applied,
            SearchDecisionWriteResult::AlreadyApplied => PublicSearchMaterializationStatus::AlreadyApplied,
            SearchDecisionWriteResult::RejectedObsolete => PublicSearchMaterializationStatus::RejectedObsolete,
            SearchDecisionWriteResult::Divergent => PublicSearchMaterializationStatus::Divergent,
        };
    }
}
