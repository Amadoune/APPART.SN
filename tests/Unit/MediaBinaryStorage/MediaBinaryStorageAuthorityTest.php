<?php

namespace Tests\Unit\MediaBinaryStorage;

use Appart\Modules\Media\Application\BinaryStorage\DeterministicMediaBinaryStorageAuthority;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryStorageStatus;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryWriteRequest;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Infrastructure\BinaryStorage\LaravelFilesystemMediaBinaryObjectStore;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MediaBinaryStorageAuthorityTest extends TestCase
{
    private const OWNER = '95000000-0000-4000-8000-000000000001';

    private const ASSET = '95000000-0000-4000-8000-000000000002';

    private const INTENT = '95000000-0000-4000-8000-000000000003';

    #[Test]
    public function real_bytes_are_stored_checksummed_and_materialized_as_a_quarantined_asset(): void
    {
        Storage::fake('media');
        $assets = new InMemoryMediaAssetStore;
        $authority = new DeterministicMediaBinaryStorageAuthority(
            new LaravelFilesystemMediaBinaryObjectStore(Storage::disk('media')),
            $assets,
        );
        $bytes = "\xFF\xD8\xFFphoto1.jpg-binary\xFF\xD9";
        $request = new MediaBinaryWriteRequest(self::OWNER, self::ASSET, self::INTENT, 'photo1.jpg', 'image/jpeg');

        $first = $authority->store($request, $this->stream($bytes));
        $replay = $authority->store($request, $this->stream($bytes));

        self::assertSame(MediaBinaryStorageStatus::Applied, $first->status);
        self::assertSame(MediaBinaryStorageStatus::AlreadyApplied, $replay->status);
        self::assertSame(hash('sha256', $bytes), $first->object->checksum);
        self::assertSame(strlen($bytes), $first->object->bytes);
        Storage::disk('media')->assertExists($first->object->storageKey);
        self::assertSame($bytes, Storage::disk('media')->get($first->object->storageKey));
        self::assertSame('quarantined', $assets->read(self::ASSET)?->state);
        self::assertSame(self::OWNER, $assets->read(self::ASSET)->payload['ownerId']);
    }

    #[Test]
    public function a_different_binary_for_the_same_owner_asset_identity_is_rejected(): void
    {
        Storage::fake('media');
        $authority = new DeterministicMediaBinaryStorageAuthority(
            new LaravelFilesystemMediaBinaryObjectStore(Storage::disk('media')),
            new InMemoryMediaAssetStore,
        );
        $request = new MediaBinaryWriteRequest(self::OWNER, self::ASSET, self::INTENT, 'photo1.jpg', 'image/jpeg');

        self::assertSame(MediaBinaryStorageStatus::Applied, $authority->store($request, $this->stream('first'))->status);
        self::assertSame(MediaBinaryStorageStatus::DivergentContent, $authority->store($request, $this->stream('second'))->status);
    }

    /** @return resource */
    private function stream(string $bytes): mixed
    {
        $stream = fopen('php://temp', 'w+b');
        self::assertIsResource($stream);
        fwrite($stream, $bytes);
        rewind($stream);

        return $stream;
    }
}

final class InMemoryMediaAssetStore implements MediaAssetStore
{
    private ?MediaAssetState $state = null;

    public function read(string $assetId): ?MediaAssetState
    {
        return $this->state?->id === $assetId ? $this->state : null;
    }

    public function save(MediaAssetState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        if ($this->state !== null) {
            return $this->state->intentId === $state->intentId && hash_equals($this->state->intentChecksum, $state->intentChecksum)
                ? MediaIngestionPersistenceWriteResult::AlreadyApplied
                : MediaIngestionPersistenceWriteResult::VersionConflict;
        }
        if ($expectedVersion !== 0) {
            return MediaIngestionPersistenceWriteResult::VersionConflict;
        }
        $this->state = $state;

        return MediaIngestionPersistenceWriteResult::Applied;
    }
}
