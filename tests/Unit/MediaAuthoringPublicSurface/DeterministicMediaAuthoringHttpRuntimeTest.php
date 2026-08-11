<?php

namespace Tests\Unit\MediaAuthoringPublicSurface;

use App\Application\MediaAuthoringHttp\DeterministicMediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpStatus;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetCommandV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetResultV1;
use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObject;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryStorageResult;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryStorageStatus;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessResult;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessStatus;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\Media\Support\FakeMediaCollectionRegistry;

final class DeterministicMediaAuthoringHttpRuntimeTest extends TestCase
{
    #[Test]
    public function certified_capabilities_are_composed_with_deterministic_owner_scoped_identities(): void
    {
        $owner = $this->id(1);
        $property = $this->id(2);
        $intent = $this->id(3);
        $commands = [];
        $binary = $this->createMock(MediaBinaryStorageAuthorityV1::class);
        $binary->expects(self::once())->method('store')->willReturnCallback(function ($request, $stream) use ($owner): MediaBinaryStorageResult {
            self::assertSame($owner, $request->ownerId);
            $contents = stream_get_contents($stream);
            self::assertSame('real-image', $contents);

            return new MediaBinaryStorageResult(MediaBinaryStorageStatus::Applied, new MediaBinaryObject($owner, $request->assetId, 'owners/'.$owner.'/assets/'.$request->assetId, hash('sha256', 'real-image'), 10));
        });
        $readiness = $this->createMock(MediaAssetReadinessV1::class);
        $readiness->expects(self::once())->method('makeReady')->willReturn(new MediaAssetReadinessResult(MediaAssetReadinessStatus::Applied, hash('sha256', 'real-image'), 2));
        $attachment = $this->createMock(AttachReadyMediaAssetV1::class);
        $attachment->expects(self::once())->method('attach')->willReturnCallback(function (AttachReadyMediaAssetCommandV1 $command) use (&$commands, $property): AttachReadyMediaAssetResultV1 {
            $commands[] = $command;
            self::assertSame($property, $command->propertyId);
            self::assertSame('owner', $command->source);

            return AttachReadyMediaAssetResultV1::Applied;
        });
        $media = $this->createMock(MediaIngestionRuntimeV1::class);
        $media->method('binary')->willReturn($binary);
        $media->method('readiness')->willReturn($readiness);
        $media->method('attachment')->willReturn($attachment);
        $runtime = new DeterministicMediaAuthoringHttpRuntime(
            $media,
            new FixedPublicSurfacePropertyStore(new PropertyAuthoringState($property, $owner, 1, $this->id(4), hash('sha256', 'property'))),
            new FakeMediaCollectionRegistry,
        );
        $stream = fopen('php://temp', 'w+b');
        self::assertIsResource($stream);
        fwrite($stream, 'real-image');
        rewind($stream);

        $result = $runtime->upload($owner, $property, $intent, 'photo.jpg', 'image/jpeg', $stream, 1, null, '2026-08-10T12:00:00+00:00');

        self::assertSame(MediaAuthoringHttpStatus::Created, $result->status);
        self::assertSame('ready', $result->data['state']);
        self::assertCount(1, $commands);
        self::assertSame($result->data['mediaId'], $commands[0]->mediaId);
        self::assertSame($result->data['collectionId'], $commands[0]->collectionId);
    }

    #[Test]
    public function foreign_owner_is_rejected_before_any_media_capability_is_called(): void
    {
        $media = $this->createMock(MediaIngestionRuntimeV1::class);
        $media->expects(self::never())->method('binary');
        $runtime = new DeterministicMediaAuthoringHttpRuntime(
            $media,
            new FixedPublicSurfacePropertyStore(new PropertyAuthoringState($this->id(10), $this->id(11), 1, $this->id(12), hash('sha256', 'property'))),
            new FakeMediaCollectionRegistry,
        );

        self::assertSame(
            MediaAuthoringHttpStatus::NotFoundOrForbidden,
            $runtime->upload($this->id(99), $this->id(10), $this->id(13), 'photo.jpg', 'image/jpeg', fopen('php://temp', 'w+b'), 1, null, '2026-08-10T12:00:00+00:00')->status,
        );
    }

    private function id(int $suffix): string
    {
        return sprintf('70000000-0000-4000-8000-%012d', $suffix);
    }
}

final readonly class FixedPublicSurfacePropertyStore implements PropertyAuthoringStore
{
    public function __construct(private PropertyAuthoringState $state) {}

    public function read(string $propertyId): ?PropertyAuthoringState
    {
        return $this->state->propertyId === $propertyId ? $this->state : null;
    }

    public function save(PropertyAuthoringState $state, int $expectedVersion): PropertyAuthoringPersistenceWriteResult
    {
        return PropertyAuthoringPersistenceWriteResult::Rejected;
    }
}
