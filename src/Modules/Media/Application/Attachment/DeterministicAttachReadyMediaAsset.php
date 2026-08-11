<?php

namespace Appart\Modules\Media\Application\Attachment;

use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentIntentStore;
use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentTransaction;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\Exception\MediaCollectionIdConflict;
use Appart\Modules\Media\Domain\Exception\MediaCollectionViolation;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Domain\Exception\PropertyUnavailable;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use Throwable;
use ValueError;

final readonly class DeterministicAttachReadyMediaAsset implements AttachReadyMediaAssetV1
{
    public function __construct(
        private MediaAssetStore $assets,
        private MediaCollectionRegistry $collections,
        private PropertyCatalog $properties,
        private MediaAttachmentIntentStore $intents,
        private MediaAttachmentTransaction $transaction,
    ) {}

    public function attach(AttachReadyMediaAssetCommandV1 $command): AttachReadyMediaAssetResultV1
    {
        try {
            return $this->transaction->run(fn (): AttachReadyMediaAssetResultV1 => $this->apply($command));
        } catch (PropertyUnavailable) {
            return AttachReadyMediaAssetResultV1::PropertyUnavailable;
        } catch (MediaCollectionIdConflict) {
            return AttachReadyMediaAssetResultV1::CollectionPropertyConflict;
        } catch (MediaIdConflict) {
            return AttachReadyMediaAssetResultV1::MediaIdConflict;
        } catch (ConcurrentMediaCollectionModification) {
            return AttachReadyMediaAssetResultV1::VersionConflict;
        } catch (MediaCollectionViolation|InvalidMediaValue|ValueError) {
            return AttachReadyMediaAssetResultV1::InvalidMedia;
        } catch (Throwable) {
            return AttachReadyMediaAssetResultV1::TemporarilyUnavailable;
        }
    }

    private function apply(AttachReadyMediaAssetCommandV1 $command): AttachReadyMediaAssetResultV1
    {
        $intent = new MediaAttachmentIntent($command->intentId, $command->intentChecksum, $command->collectionId, $command->propertyId, $command->mediaId);
        $replay = $this->intents->find($command->intentId);
        if ($replay !== null) {
            return $this->replay($replay, $intent);
        }
        $asset = $this->assets->read($command->mediaId);
        if ($asset === null || $asset->state !== 'ready' || ($asset->payload['contentChecksum'] ?? null) !== strtolower($command->contentChecksum)) {
            return AttachReadyMediaAssetResultV1::InvalidMedia;
        }
        $collectionId = MediaCollectionId::fromString($command->collectionId);
        $propertyId = PropertyId::fromString($command->propertyId);
        $collection = $this->collections->find($collectionId);
        if ($collection === null) {
            if (! $this->properties->exists($propertyId)) {
                return AttachReadyMediaAssetResultV1::PropertyUnavailable;
            }
            $collection = MediaCollection::create($collectionId, $propertyId, new DateTimeImmutable($command->occurredAt));
            $this->collections->add($collection);
        } elseif (! $collection->propertyId()->equals($propertyId)) {
            return AttachReadyMediaAssetResultV1::CollectionPropertyConflict;
        }
        if ($command->expectedCollectionVersion !== null && $collection->version() !== $command->expectedCollectionVersion) {
            return AttachReadyMediaAssetResultV1::VersionConflict;
        }
        if (! $this->intents->reserve($intent)) {
            $existing = $this->intents->find($command->intentId);

            return $existing === null ? AttachReadyMediaAssetResultV1::TemporarilyUnavailable : $this->replay($existing, $intent);
        }
        $expected = $collection->version();
        $collection->add(
            MediaId::fromString($command->mediaId),
            MediaType::Image,
            MediaChecksum::fromSha256($command->contentChecksum),
            MediaOrder::fromInt($command->order),
            $command->caption === null ? null : MediaCaption::fromString($command->caption),
            MediaSource::from($command->source),
            new DateTimeImmutable($command->occurredAt),
        );
        $this->collections->saveWithMediaReservation($collection, MediaId::fromString($command->mediaId), $expected);
        $this->intents->markApplied($command->intentId, $collection->version());

        return AttachReadyMediaAssetResultV1::Applied;
    }

    private function replay(MediaAttachmentIntent $existing, MediaAttachmentIntent $candidate): AttachReadyMediaAssetResultV1
    {
        if ($existing->checksum !== $candidate->checksum
            || $existing->collectionId !== $candidate->collectionId
            || $existing->propertyId !== $candidate->propertyId
            || $existing->mediaId !== $candidate->mediaId) {
            return AttachReadyMediaAssetResultV1::DivergentIntent;
        }

        return $existing->aggregateVersion === null
            ? AttachReadyMediaAssetResultV1::TemporarilyUnavailable
            : AttachReadyMediaAssetResultV1::AlreadyApplied;
    }
}
