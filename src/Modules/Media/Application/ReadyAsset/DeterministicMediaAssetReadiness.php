<?php

namespace Appart\Modules\Media\Application\ReadyAsset;

use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryObjectStore;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObject;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObjectStoreStatus;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;
use Throwable;

final readonly class DeterministicMediaAssetReadiness implements MediaAssetReadinessV1
{
    public function __construct(
        private MediaBinaryObjectStore $objects,
        private MediaAssetStore $assets,
    ) {}

    public function makeReady(MediaAssetReadinessRequest $request): MediaAssetReadinessResult
    {
        try {
            $current = $this->assets->read($request->assetId);
            if ($current === null) {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::Missing);
            }
            if (! in_array($current->state, ['quarantined', 'ready'], true)) {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::InvalidState);
            }
            $ownerId = $this->payloadString($current, 'ownerId');
            $expectedChecksum = $this->payloadString($current, 'contentChecksum');
            $expectedStorageKey = $this->payloadString($current, 'storageKey');
            $expectedBytes = $this->payloadInt($current, 'bytes');
            if ($ownerId === null || $expectedChecksum === null || $expectedStorageKey === null || $expectedBytes === null) {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::IntegrityFailure);
            }
            $inspection = $this->objects->inspect($ownerId, $current->id);
            if ($inspection->status === MediaBinaryObjectStoreStatus::Missing) {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::IntegrityFailure);
            }
            if ($inspection->status === MediaBinaryObjectStoreStatus::DependencyUnavailable || $inspection->object === null) {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::DependencyUnavailable);
            }
            if (! $this->matches($inspection->object, $ownerId, $expectedStorageKey, $expectedChecksum, $expectedBytes)) {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::IntegrityFailure);
            }
            if ($current->state === 'ready') {
                return new MediaAssetReadinessResult(MediaAssetReadinessStatus::AlreadyApplied, $expectedChecksum, $current->version);
            }
            $candidate = new MediaAssetState(
                $current->id,
                'ready',
                $current->version + 1,
                strtolower($request->intentId),
                $this->intentChecksum($request, $current),
                $current->payload,
            );
            $write = $this->assets->save($candidate, $current->version);

            return match ($write) {
                MediaIngestionPersistenceWriteResult::Applied => new MediaAssetReadinessResult(MediaAssetReadinessStatus::Applied, $expectedChecksum, $candidate->version),
                MediaIngestionPersistenceWriteResult::AlreadyApplied => new MediaAssetReadinessResult(MediaAssetReadinessStatus::AlreadyApplied, $expectedChecksum, $candidate->version),
                MediaIngestionPersistenceWriteResult::VersionConflict => $this->afterConflict($request->assetId, $expectedChecksum),
                MediaIngestionPersistenceWriteResult::DivergentIntent,
                MediaIngestionPersistenceWriteResult::IdentityConflict => new MediaAssetReadinessResult(MediaAssetReadinessStatus::VersionConflict),
                MediaIngestionPersistenceWriteResult::Rejected => new MediaAssetReadinessResult(MediaAssetReadinessStatus::DependencyUnavailable),
            };
        } catch (Throwable) {
            return new MediaAssetReadinessResult(MediaAssetReadinessStatus::DependencyUnavailable);
        }
    }

    private function matches(MediaBinaryObject $object, string $ownerId, string $storageKey, string $checksum, int $bytes): bool
    {
        return hash_equals(strtolower($ownerId), strtolower($object->ownerId))
            && hash_equals($storageKey, $object->storageKey)
            && hash_equals($checksum, $object->checksum)
            && $bytes === $object->bytes;
    }

    private function payloadString(MediaAssetState $state, string $key): ?string
    {
        $value = $state->payload[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function payloadInt(MediaAssetState $state, string $key): ?int
    {
        $value = $state->payload[$key] ?? null;

        return is_int($value) && $value >= 0 ? $value : null;
    }

    private function intentChecksum(MediaAssetReadinessRequest $request, MediaAssetState $state): string
    {
        $payload = $state->payload;
        ksort($payload);

        return hash('sha256', (string) json_encode([
            'assetId' => strtolower($request->assetId),
            'intentId' => strtolower($request->intentId),
            'operation' => 'MediaAssetReadinessV1',
            'payload' => $payload,
            'sourceVersion' => $state->version,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    private function afterConflict(string $assetId, string $checksum): MediaAssetReadinessResult
    {
        $current = $this->assets->read($assetId);
        if ($current !== null && $current->state === 'ready' && ($current->payload['contentChecksum'] ?? null) === $checksum) {
            return new MediaAssetReadinessResult(MediaAssetReadinessStatus::AlreadyApplied, $checksum, $current->version);
        }

        return new MediaAssetReadinessResult(MediaAssetReadinessStatus::VersionConflict);
    }
}
