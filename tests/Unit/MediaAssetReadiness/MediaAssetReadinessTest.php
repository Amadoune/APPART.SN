<?php

namespace Tests\Unit\MediaAssetReadiness;

use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryObjectStore;
use Appart\Modules\Media\Application\BinaryStorage\DeterministicMediaBinaryStorageAuthority;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObject;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObjectStoreResult;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObjectStoreStatus;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryWriteRequest;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\ReadyAsset\DeterministicMediaAssetReadiness;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessRequest;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaAssetReadinessTest extends TestCase
{
    #[Test]
    public function a_real_quarantined_blob_becomes_ready_without_mutating_its_identity(): void
    {
        $objects = new InMemoryReadinessObjectStore;
        $assets = new InMemoryReadinessAssetStore;
        $binary = new DeterministicMediaBinaryStorageAuthority($objects, $assets);
        $stream = fopen('php://temp', 'w+b');
        self::assertIsResource($stream);
        fwrite($stream, 'unchanged-real-image-bytes');
        rewind($stream);
        $request = new MediaBinaryWriteRequest($this->id(1), $this->id(2), $this->id(3), 'photo.jpg', 'image/jpeg');
        $binary->store($request, $stream);
        $before = $assets->read($request->assetId);
        self::assertNotNull($before);
        $blobBefore = $objects->contents;

        $readiness = new DeterministicMediaAssetReadiness($objects, $assets);
        $result = $readiness->makeReady(new MediaAssetReadinessRequest($request->assetId, $this->id(4)));
        $after = $assets->read($request->assetId);

        self::assertSame(MediaAssetReadinessStatus::Applied, $result->status);
        self::assertSame('ready', $after->state);
        self::assertSame(2, $after->version);
        self::assertSame($before->payload, $after->payload);
        self::assertSame($blobBefore, $objects->contents);
        self::assertSame($before->payload['contentChecksum'], $result->checksum);

        $replay = $readiness->makeReady(new MediaAssetReadinessRequest($request->assetId, $this->id(5)));
        self::assertSame(MediaAssetReadinessStatus::AlreadyApplied, $replay->status);
        self::assertSame(2, $assets->read($request->assetId)?->version);
    }

    #[Test]
    public function corrupted_blob_is_rejected_and_the_asset_stays_quarantined(): void
    {
        $objects = new InMemoryReadinessObjectStore;
        $assets = new InMemoryReadinessAssetStore;
        $binary = new DeterministicMediaBinaryStorageAuthority($objects, $assets);
        $stream = fopen('php://temp', 'w+b');
        self::assertIsResource($stream);
        fwrite($stream, 'original');
        rewind($stream);
        $binary->store(new MediaBinaryWriteRequest($this->id(10), $this->id(11), $this->id(12), 'photo.jpg', 'image/jpeg'), $stream);
        $objects->contents = 'corrupted';

        $result = (new DeterministicMediaAssetReadiness($objects, $assets))
            ->makeReady(new MediaAssetReadinessRequest($this->id(11), $this->id(13)));

        self::assertSame(MediaAssetReadinessStatus::IntegrityFailure, $result->status);
        self::assertSame('quarantined', $assets->read($this->id(11))->state);
        self::assertSame(1, $assets->read($this->id(11))->version);
    }

    private function id(int $suffix): string
    {
        return sprintf('65000000-0000-4000-8000-%012d', $suffix);
    }
}

final class InMemoryReadinessObjectStore implements MediaBinaryObjectStore
{
    public ?string $contents = null;

    private ?MediaBinaryObject $object = null;

    public function store(MediaBinaryWriteRequest $request, mixed $stream): MediaBinaryObjectStoreResult
    {
        $contents = is_resource($stream) ? stream_get_contents($stream) : false;
        if (! is_string($contents)) {
            return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
        }
        $this->contents = $contents;
        $this->object = new MediaBinaryObject(
            strtolower($request->ownerId),
            strtolower($request->assetId),
            'owners/'.strtolower($request->ownerId).'/assets/'.strtolower($request->assetId),
            hash('sha256', $contents),
            strlen($contents),
        );

        return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::Applied, $this->object);
    }

    public function inspect(string $ownerId, string $assetId): MediaBinaryObjectStoreResult
    {
        if ($this->object === null || $this->contents === null) {
            return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::Missing);
        }

        return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::AlreadyApplied, new MediaBinaryObject(
            $this->object->ownerId,
            $this->object->assetId,
            $this->object->storageKey,
            hash('sha256', $this->contents),
            strlen($this->contents),
        ));
    }

    public function delete(string $ownerId, string $assetId): void
    {
        $this->contents = null;
        $this->object = null;
    }
}

final class InMemoryReadinessAssetStore implements MediaAssetStore
{
    private ?MediaAssetState $state = null;

    public function read(string $assetId): ?MediaAssetState
    {
        return $this->state?->id === strtolower($assetId) ? $this->state : null;
    }

    public function save(MediaAssetState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        if ($this->state !== null && $this->state->intentId === $state->intentId) {
            return $this->state->intentChecksum === $state->intentChecksum
                ? MediaIngestionPersistenceWriteResult::AlreadyApplied
                : MediaIngestionPersistenceWriteResult::DivergentIntent;
        }
        $version = $this->state !== null ? $this->state->version : 0;
        if ($expectedVersion !== $version) {
            return MediaIngestionPersistenceWriteResult::VersionConflict;
        }
        $this->state = $state;

        return MediaIngestionPersistenceWriteResult::Applied;
    }
}
