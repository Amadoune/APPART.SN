<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryObjectStore;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Throwable;

final readonly class DeterministicMediaBinaryStorageAuthority implements MediaBinaryStorageAuthorityV1
{
    public function __construct(
        private MediaBinaryObjectStore $objects,
        private MediaAssetStore $assets,
    ) {}

    public function store(MediaBinaryWriteRequest $request, mixed $stream): MediaBinaryStorageResult
    {
        $stored = $this->objects->store($request, $stream);
        if ($stored->status === MediaBinaryObjectStoreStatus::DivergentContent) {
            return new MediaBinaryStorageResult(MediaBinaryStorageStatus::DivergentContent);
        }
        if ($stored->status === MediaBinaryObjectStoreStatus::DependencyUnavailable || $stored->object === null) {
            return new MediaBinaryStorageResult(MediaBinaryStorageStatus::DependencyUnavailable);
        }

        $object = $stored->object;
        try {
            $current = $this->assets->read($request->assetId);
            if ($current !== null) {
                return $this->sameAsset($current, $object)
                    ? new MediaBinaryStorageResult(MediaBinaryStorageStatus::AlreadyApplied, $object)
                    : new MediaBinaryStorageResult(MediaBinaryStorageStatus::DivergentContent);
            }
            $payload = [
                'ownerId' => strtolower($request->ownerId),
                'storageKey' => $object->storageKey,
                'contentChecksum' => $object->checksum,
                'bytes' => $object->bytes,
                'originalName' => $request->originalName,
                'contentType' => $request->contentType,
            ];
            $checksum = $this->intentChecksum($request, $payload);
            $write = $this->assets->save(new MediaAssetState(
                strtolower($request->assetId),
                'quarantined',
                1,
                strtolower($request->intentId),
                $checksum,
                $payload,
            ), 0);
            if (in_array($write, [MediaIngestionPersistenceWriteResult::Applied, MediaIngestionPersistenceWriteResult::AlreadyApplied], true)) {
                return new MediaBinaryStorageResult(
                    $write === MediaIngestionPersistenceWriteResult::Applied
                        ? MediaBinaryStorageStatus::Applied
                        : MediaBinaryStorageStatus::AlreadyApplied,
                    $object,
                );
            }
        } catch (Throwable) {
            // The newly-created blob is compensated below; no infrastructure detail escapes.
        }

        if ($stored->status === MediaBinaryObjectStoreStatus::Applied) {
            $this->objects->delete($request->ownerId, $request->assetId);
        }

        return new MediaBinaryStorageResult(MediaBinaryStorageStatus::DependencyUnavailable);
    }

    private function sameAsset(MediaAssetState $state, MediaBinaryObject $object): bool
    {
        return ($state->payload['ownerId'] ?? null) === strtolower($object->ownerId)
            && ($state->payload['storageKey'] ?? null) === $object->storageKey
            && ($state->payload['contentChecksum'] ?? null) === $object->checksum
            && ($state->payload['bytes'] ?? null) === $object->bytes;
    }

    /** @param array<string, int|string> $payload */
    private function intentChecksum(MediaBinaryWriteRequest $request, array $payload): string
    {
        ksort($payload);

        return hash('sha256', (string) json_encode([
            'assetId' => strtolower($request->assetId),
            'intentId' => strtolower($request->intentId),
            'payload' => $payload,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
