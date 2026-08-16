<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotWriter;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoCanonicalPathPolicyV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoMaterializationSourceReaderV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadStatus;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Throwable;

final readonly class DeterministicContentSeoSnapshotMaterializerV1 implements MaterializeContentSeoSnapshotV1
{
    public function __construct(
        private ContentSeoMaterializationSourceReaderV1 $sources,
        private ContentSeoCanonicalPathPolicyV1 $canonicalPaths,
        private CanonicalPolicy $canonicals,
        private ContentSeoSnapshotIdentityV1 $identities,
        private ContentSeoSourceSnapshotReader $reader,
        private ContentSeoSourceSnapshotWriter $writer,
    ) {}

    public function materialize(ListingId $listingId): ContentSeoMaterializationResult
    {
        try {
            $read = $this->sources->read($listingId);
            if ($read->status !== ContentSeoMaterializationSourceStatus::Found || $read->sources === null) {
                return new ContentSeoMaterializationResult(match ($read->status) {
                    ContentSeoMaterializationSourceStatus::Missing => ContentSeoMaterializationStatus::SourceMissing,
                    ContentSeoMaterializationSourceStatus::NotReady => ContentSeoMaterializationStatus::SourceNotReady,
                    ContentSeoMaterializationSourceStatus::Corrupted => ContentSeoMaterializationStatus::SourceCorrupted,
                    ContentSeoMaterializationSourceStatus::DependencyUnavailable => ContentSeoMaterializationStatus::DependencyUnavailable,
                    ContentSeoMaterializationSourceStatus::Found => ContentSeoMaterializationStatus::SourceCorrupted,
                });
            }

            $sources = $read->sources;
            $canonicalPath = $this->canonicalPaths->decide($listingId);
            $canonical = $this->canonicals->fromPath($canonicalPath);
            $current = $this->reader->readByListing($listingId);
            if ($current->status === ContentSeoSnapshotReadStatus::Corrupted) {
                return new ContentSeoMaterializationResult(ContentSeoMaterializationStatus::SourceCorrupted);
            }

            $version = 1;
            $history = [new CanonicalHistoryEntry($canonical, CanonicalDisposition::Current, $sources->decisionAt)];
            if ($current->status === ContentSeoSnapshotReadStatus::Found && $current->snapshot !== null) {
                $relation = $this->relation($sources, $current->snapshot);
                if ($relation === 'older') {
                    return new ContentSeoMaterializationResult(ContentSeoMaterializationStatus::RejectedObsolete, $current->snapshot);
                }
                if ($relation === 'divergent') {
                    return new ContentSeoMaterializationResult(ContentSeoMaterializationStatus::Divergent, $current->snapshot);
                }
                $version = $relation === 'same' ? $current->snapshot->version : $current->snapshot->version + 1;
                $history = $current->snapshot->canonicalHistory;
            }

            $listing = new ListingSeoSource(
                $sources->listing->listingId,
                $sources->listing->state,
                $sources->listing->headline,
                $sources->listing->description,
                $canonicalPath,
                $sources->listing->revision,
                $sources->listing->publishedAt,
                $sources->listing->expiresAt,
                $sources->listing->expiredTreatment,
                $sources->listing->nonIndexablePageTreatment,
            );
            $snapshot = new ContentSeoSourceDecision(
                $this->identities->snapshotId($listingId),
                $listingId,
                $version,
                $listing,
                $sources->search,
                $sources->property,
                $history,
                $sources->decisionAt,
            );

            return new ContentSeoMaterializationResult($this->status($this->writer->store($snapshot)), $snapshot);
        } catch (Throwable) {
            return new ContentSeoMaterializationResult(ContentSeoMaterializationStatus::DependencyUnavailable);
        }
    }

    private function relation(ContentSeoMaterializationSources $candidate, ContentSeoSourceDecision $current): string
    {
        $candidateRevisions = [$candidate->listing->revision, $candidate->search->revision, $candidate->property->revision];
        $currentRevisions = [$current->listing->revision, $current->search->revision, $current->property->revision];
        $greater = false;
        $lower = false;
        foreach (SeoSourceKind::cases() as $index => $source) {
            $next = $candidateRevisions[$index];
            $stored = $currentRevisions[$index];
            if ($next->source !== $source || $stored->source !== $source) {
                return 'divergent';
            }
            if ($next->version === $stored->version && ! $next->identicalTo($stored)) {
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

    private function status(ContentSeoSnapshotWriteResult $result): ContentSeoMaterializationStatus
    {
        return match ($result) {
            ContentSeoSnapshotWriteResult::Applied => ContentSeoMaterializationStatus::Applied,
            ContentSeoSnapshotWriteResult::AlreadyApplied => ContentSeoMaterializationStatus::AlreadyApplied,
            ContentSeoSnapshotWriteResult::RejectedObsolete => ContentSeoMaterializationStatus::RejectedObsolete,
            ContentSeoSnapshotWriteResult::Divergent => ContentSeoMaterializationStatus::Divergent,
        };
    }
}
