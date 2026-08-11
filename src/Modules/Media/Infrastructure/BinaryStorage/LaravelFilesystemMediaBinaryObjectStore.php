<?php

namespace Appart\Modules\Media\Infrastructure\BinaryStorage;

use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryObjectStore;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObject;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObjectStoreResult;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObjectStoreStatus;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryWriteRequest;
use Illuminate\Contracts\Filesystem\Filesystem;
use Throwable;

final readonly class LaravelFilesystemMediaBinaryObjectStore implements MediaBinaryObjectStore
{
    public function __construct(private Filesystem $disk) {}

    public function store(MediaBinaryWriteRequest $request, mixed $stream): MediaBinaryObjectStoreResult
    {
        if (! is_resource($stream)) {
            return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
        }
        try {
            $contents = stream_get_contents($stream);
            if (! is_string($contents)) {
                return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
            }
            $object = $this->object($request, $contents);
            if ($this->disk->exists($object->storageKey)) {
                $existing = $this->disk->get($object->storageKey);

                return is_string($existing) && hash_equals(hash('sha256', $existing), $object->checksum)
                    ? new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::AlreadyApplied, $object)
                    : new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DivergentContent);
            }
            if (! $this->disk->put($object->storageKey, $contents, ['visibility' => 'private'])) {
                return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
            }
            $persisted = $this->disk->get($object->storageKey);
            if (! is_string($persisted) || ! hash_equals(hash('sha256', $persisted), $object->checksum)) {
                $this->disk->delete($object->storageKey);

                return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
            }

            return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::Applied, $object);
        } catch (Throwable) {
            return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
        }
    }

    public function delete(string $ownerId, string $assetId): void
    {
        $this->disk->delete($this->key($ownerId, $assetId));
    }

    public function inspect(string $ownerId, string $assetId): MediaBinaryObjectStoreResult
    {
        try {
            $key = $this->key($ownerId, $assetId);
            if (! $this->disk->exists($key)) {
                return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::Missing);
            }
            $contents = $this->disk->get($key);
            if (! is_string($contents)) {
                return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
            }

            return new MediaBinaryObjectStoreResult(
                MediaBinaryObjectStoreStatus::AlreadyApplied,
                new MediaBinaryObject(
                    strtolower($ownerId),
                    strtolower($assetId),
                    $key,
                    hash('sha256', $contents),
                    strlen($contents),
                ),
            );
        } catch (Throwable) {
            return new MediaBinaryObjectStoreResult(MediaBinaryObjectStoreStatus::DependencyUnavailable);
        }
    }

    private function object(MediaBinaryWriteRequest $request, string $contents): MediaBinaryObject
    {
        return new MediaBinaryObject(
            strtolower($request->ownerId),
            strtolower($request->assetId),
            $this->key($request->ownerId, $request->assetId),
            hash('sha256', $contents),
            strlen($contents),
        );
    }

    private function key(string $ownerId, string $assetId): string
    {
        return 'owners/'.strtolower($ownerId).'/assets/'.strtolower($assetId);
    }
}
