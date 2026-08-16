<?php

namespace App\Application\PublicMediaMaterialization;

use App\Application\PublicMediaMaterialization\Contract\CatchUpPublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\MaterializePublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\PublicMediaOwnerSourceReaderV2;
use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaRevision\PublicMediaRevisionChecksum;
use App\Application\PublicMediaRevision\PublicMediaRevisionVersion;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionWriter;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicMediaSource\PublicMediaItemV2;
use App\Application\PublicMediaSource\PublicMediaReadStatus;
use App\Application\PublicMediaSource\PublicMediaSourceRevisionRelationV2;
use App\Application\PublicMediaSource\PublicMediaSourceRevisionV2;
use App\Application\PublicMediaSource\PublicMediaWriteResult;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Throwable;

final readonly class DeterministicPublicMediaDecisionMaterializerV2 implements CatchUpPublicMediaDecisionV2, MaterializePublicMediaDecisionV2
{
    public function __construct(
        private PublicMediaOwnerSourceReaderV2 $sources,
        private PublicMediaDecisionReader $reader,
        private PublicMediaDecisionWriter $writer,
    ) {}

    public function materialize(ListingId $listingId): PublicMediaMaterializationResult
    {
        try {
            $read = $this->sources->read($listingId);
            if ($read->status !== PublicMediaOwnerSourceStatus::Ready || $read->source === null) {
                return new PublicMediaMaterializationResult(match ($read->status) {
                    PublicMediaOwnerSourceStatus::Missing => PublicMediaMaterializationStatus::SourceMissing,
                    PublicMediaOwnerSourceStatus::NotReady => PublicMediaMaterializationStatus::SourceNotReady,
                    PublicMediaOwnerSourceStatus::Corrupted => PublicMediaMaterializationStatus::SourceCorrupted,
                    PublicMediaOwnerSourceStatus::DependencyUnavailable => PublicMediaMaterializationStatus::DependencyUnavailable,
                    PublicMediaOwnerSourceStatus::Ready => PublicMediaMaterializationStatus::SourceCorrupted,
                });
            }
            $source = $read->source;
            $items = array_map(static fn (PublicMediaOwnerItemV2 $item): PublicMediaItemV2 => new PublicMediaItemV2(
                $item->mediaId,
                '/media/'.$item->mediaId.'/revisions/'.$item->assetVersion,
                $item->assetVersion,
                $item->order,
                $item->primary,
            ), $source->items);
            $revisionSource = new PublicMediaSourceRevisionV2(
                $source->listingId,
                $source->publicationVersion,
                $source->listingState,
                $source->mediaCollectionId,
                $source->collectionVersion,
                array_map(static fn (PublicMediaOwnerItemV2 $item): array => ['mediaId' => $item->mediaId, 'aggregateVersion' => $item->attachmentVersion, 'intentChecksum' => $item->attachmentChecksum], $source->items),
                array_map(static fn (PublicMediaOwnerItemV2 $item): array => ['mediaId' => $item->mediaId, 'assetVersion' => $item->assetVersion, 'state' => $item->assetState, 'contentChecksum' => $item->contentChecksum], $source->items),
            );
            $current = $this->reader->read($source->mediaCollectionId);
            if ($current->status === PublicMediaReadStatus::Corrupted) {
                return new PublicMediaMaterializationResult(PublicMediaMaterializationStatus::SourceCorrupted);
            }
            $version = 1;
            if ($current->status === PublicMediaReadStatus::Found && $current->decision !== null) {
                if ($current->decision->schemaVersion === 2 && $current->decision->sourceRevisionV2 !== null) {
                    $relation = $revisionSource->compareTo($current->decision->sourceRevisionV2);
                    if ($relation === PublicMediaSourceRevisionRelationV2::Older) {
                        return new PublicMediaMaterializationResult(PublicMediaMaterializationStatus::RejectedObsolete, $current->decision);
                    }
                    if ($relation === PublicMediaSourceRevisionRelationV2::Incomparable) {
                        return new PublicMediaMaterializationResult(PublicMediaMaterializationStatus::Divergent, $current->decision);
                    }
                    if ($relation === PublicMediaSourceRevisionRelationV2::Equal) {
                        return new PublicMediaMaterializationResult(PublicMediaMaterializationStatus::AlreadyApplied, $current->decision);
                    }
                }
                $version = $current->decision->revision->version->value + 1;
            }
            $payload = json_encode([
                'schemaVersion' => 2,
                'sourceRevision' => $revisionSource->canonicalData(),
                'sourceRevisionChecksum' => $revisionSource->checksum(),
                'items' => array_map(static fn (PublicMediaItemV2 $item): array => $item->canonicalData(), $items),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $primary = array_values(array_filter($items, static fn (PublicMediaItemV2 $item): bool => $item->primary))[0];
            $gallery = array_map(static fn (PublicMediaItemV2 $item): PublicMediaItem => new PublicMediaItem($item->mediaId, $item->publicLocator, []), $items);
            $decision = new PublicMediaDecision(
                $source->mediaCollectionId,
                new PublicMediaRevision(PublicMediaRevisionVersion::fromInt($version), PublicMediaRevisionChecksum::fromCanonicalPayload($payload), $revisionSource->checksum()),
                new PublicMediaItem($primary->mediaId, $primary->publicLocator, []),
                $gallery,
                2,
                $items,
                $revisionSource,
            );

            return new PublicMediaMaterializationResult(match ($this->writer->store($decision)) {
                PublicMediaWriteResult::Applied => PublicMediaMaterializationStatus::Applied,
                PublicMediaWriteResult::AlreadyApplied => PublicMediaMaterializationStatus::AlreadyApplied,
                PublicMediaWriteResult::RejectedObsolete => PublicMediaMaterializationStatus::RejectedObsolete,
                PublicMediaWriteResult::Divergent => PublicMediaMaterializationStatus::Divergent,
            }, $decision);
        } catch (Throwable) {
            return new PublicMediaMaterializationResult(PublicMediaMaterializationStatus::DependencyUnavailable);
        }
    }
}
