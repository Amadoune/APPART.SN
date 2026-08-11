<?php

namespace App\Application\MediaAuthoringHttp;

use App\Application\MediaAuthoringHttp\Contract\MediaAuthoringHttpRuntime;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetCommandV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetResultV1;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryStorageStatus;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryWriteRequest;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessRequest;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessStatus;
use Appart\Modules\Media\Application\UseCase\ArchiveMedia;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicMediaAuthoringHttpRuntime implements MediaAuthoringHttpRuntime
{
    public function __construct(
        private MediaIngestionRuntimeV1 $media,
        private PropertyAuthoringStore $properties,
        private MediaCollectionRegistry $collections,
    ) {}

    public function upload(string $ownerAccountId, string $propertyId, string $intentId, string $originalName, string $contentType, mixed $stream, int $order, ?string $caption, string $occurredAt): MediaAuthoringHttpResult
    {
        if (! $this->owns($ownerAccountId, $propertyId)) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::NotFoundOrForbidden);
        }
        $assetId = $this->uuid('asset:'.$ownerAccountId.':'.$propertyId.':'.$intentId);
        $collectionId = $this->uuid('collection:'.$propertyId);
        $binary = $this->media->binary()->store(new MediaBinaryWriteRequest(
            $ownerAccountId,
            $assetId,
            $intentId,
            $originalName,
            $contentType,
        ), $stream);
        if ($binary->status === MediaBinaryStorageStatus::DivergentContent) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Conflict);
        }
        if (! in_array($binary->status, [MediaBinaryStorageStatus::Applied, MediaBinaryStorageStatus::AlreadyApplied], true) || $binary->object === null) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Unavailable);
        }
        $ready = $this->media->readiness()->makeReady(new MediaAssetReadinessRequest($assetId, $this->uuid('ready:'.$intentId)));
        if (! in_array($ready->status, [MediaAssetReadinessStatus::Applied, MediaAssetReadinessStatus::AlreadyApplied], true)) {
            return new MediaAuthoringHttpResult(
                $ready->status === MediaAssetReadinessStatus::IntegrityFailure ? MediaAuthoringHttpStatus::Conflict : MediaAuthoringHttpStatus::Unavailable,
            );
        }
        $attachmentIntentId = $this->uuid('attach:'.$intentId);
        $attachmentChecksum = hash('sha256', implode('|', [$collectionId, $propertyId, $assetId, $binary->object->checksum, (string) $order, $caption ?? '', 'owner']));
        $attached = $this->media->attachment()->attach(new AttachReadyMediaAssetCommandV1(
            $attachmentIntentId,
            $attachmentChecksum,
            $collectionId,
            $propertyId,
            $assetId,
            $binary->object->checksum,
            $order,
            $caption,
            'owner',
            null,
            $occurredAt,
        ));
        if (! in_array($attached, [AttachReadyMediaAssetResultV1::Applied, AttachReadyMediaAssetResultV1::AlreadyApplied], true)) {
            return new MediaAuthoringHttpResult(match ($attached) {
                AttachReadyMediaAssetResultV1::PropertyUnavailable => MediaAuthoringHttpStatus::NotFoundOrForbidden,
                AttachReadyMediaAssetResultV1::DivergentIntent,
                AttachReadyMediaAssetResultV1::CollectionPropertyConflict,
                AttachReadyMediaAssetResultV1::MediaIdConflict,
                AttachReadyMediaAssetResultV1::VersionConflict,
                AttachReadyMediaAssetResultV1::InvalidMedia => MediaAuthoringHttpStatus::Conflict,
                AttachReadyMediaAssetResultV1::TemporarilyUnavailable => MediaAuthoringHttpStatus::Unavailable,
            });
        }

        return new MediaAuthoringHttpResult(
            $attached === AttachReadyMediaAssetResultV1::Applied ? MediaAuthoringHttpStatus::Created : MediaAuthoringHttpStatus::Available,
            ['propertyId' => $propertyId, 'collectionId' => $collectionId, 'mediaId' => $assetId, 'state' => 'ready', 'checksum' => $binary->object->checksum],
        );
    }

    public function collection(string $ownerAccountId, string $propertyId): MediaAuthoringHttpResult
    {
        if (! $this->owns($ownerAccountId, $propertyId)) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::NotFoundOrForbidden);
        }
        $collectionId = $this->uuid('collection:'.$propertyId);
        $collection = $this->collections->find(MediaCollectionId::fromString($collectionId));
        if ($collection === null) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Empty, ['propertyId' => $propertyId, 'collectionId' => $collectionId, 'items' => []]);
        }
        if (! $collection->propertyId()->equals(PropertyId::fromString($propertyId))) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::NotFoundOrForbidden);
        }
        $items = array_map(static fn ($item): array => [
            'mediaId' => $item->id->value,
            'type' => $item->type->value,
            'checksum' => $item->checksum->value,
            'order' => $item->order->value,
            'caption' => $item->caption?->value,
            'status' => $item->status->value,
            'primary' => $item->primary,
        ], $collection->items());

        return new MediaAuthoringHttpResult($items === [] ? MediaAuthoringHttpStatus::Empty : MediaAuthoringHttpStatus::Available, [
            'propertyId' => $propertyId,
            'collectionId' => $collectionId,
            'version' => $collection->version(),
            'items' => $items,
        ]);
    }

    public function archive(string $ownerAccountId, string $propertyId, string $mediaId, ?string $replacementMediaId, string $occurredAt): MediaAuthoringHttpResult
    {
        if (! $this->owns($ownerAccountId, $propertyId)) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::NotFoundOrForbidden);
        }
        $collectionId = $this->uuid('collection:'.$propertyId);
        $collection = $this->collections->find(MediaCollectionId::fromString($collectionId));
        if ($collection === null || ! $collection->propertyId()->equals(PropertyId::fromString($propertyId))) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::NotFoundOrForbidden);
        }
        try {
            $updated = (new ArchiveMedia($this->collections))->execute(
                MediaCollectionId::fromString($collectionId),
                MediaId::fromString($mediaId),
                $replacementMediaId === null ? null : MediaId::fromString($replacementMediaId),
                new DateTimeImmutable($occurredAt),
            );

            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Available, ['propertyId' => $propertyId, 'collectionId' => $collectionId, 'version' => $updated->version()]);
        } catch (Throwable) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Conflict);
        }
    }

    private function owns(string $ownerAccountId, string $propertyId): bool
    {
        $property = $this->properties->read($propertyId);

        return $property !== null && hash_equals($property->ownerAccountId, $ownerAccountId);
    }

    private function uuid(string $value): string
    {
        $hash = hash('sha256', $value);

        return sprintf('%s-%s-5%s-%s%s-%s', substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 13, 3), dechex((hexdec($hash[16]) & 0x3) | 0x8), substr($hash, 17, 3), substr($hash, 20, 12));
    }
}
