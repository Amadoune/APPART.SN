<?php

namespace Tests\Unit\MediaReadyAssetAttachment;

use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetCommandV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetResultV1;
use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentIntentStore;
use Appart\Modules\Media\Application\Attachment\Contract\MediaAttachmentTransaction;
use Appart\Modules\Media\Application\Attachment\DeterministicAttachReadyMediaAsset;
use Appart\Modules\Media\Application\Attachment\MediaAttachmentIntent;
use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Closure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\Media\Support\FakeMediaCollectionRegistry;

final class MediaReadyAssetAttachmentTest extends TestCase
{
    #[Test]
    public function ready_asset_is_attached_once_and_replay_is_idempotent(): void
    {
        $registry = new FakeMediaCollectionRegistry;
        $property = PropertyId::fromString($this->id(1));
        $collection = MediaCollection::create(MediaCollectionId::fromString($this->id(2)), $property, new \DateTimeImmutable('2026-08-10T10:00:00+00:00'));
        $registry->add($collection);
        $service = $this->service('ready', $registry);
        $command = $this->command();

        self::assertSame(AttachReadyMediaAssetResultV1::Applied, $service->attach($command));
        self::assertSame(AttachReadyMediaAssetResultV1::AlreadyApplied, $service->attach($command));
        $reloaded = $registry->find(MediaCollectionId::fromString($command->collectionId));
        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->items());
        self::assertSame($command->mediaId, $reloaded->items()[0]->id->value);
        self::assertSame($command->contentChecksum, $reloaded->items()[0]->checksum->value);
        self::assertSame(1, $reloaded->version());
    }

    #[Test]
    public function non_ready_asset_is_rejected_without_collection_mutation(): void
    {
        $registry = new FakeMediaCollectionRegistry;
        $command = $this->command();
        $service = $this->service('quarantined', $registry);

        self::assertSame(AttachReadyMediaAssetResultV1::InvalidMedia, $service->attach($command));
        self::assertNull($registry->find(MediaCollectionId::fromString($command->collectionId)));
    }

    private function service(string $state, FakeMediaCollectionRegistry $registry): DeterministicAttachReadyMediaAsset
    {
        $command = $this->command();
        $asset = new MediaAssetState($command->mediaId, $state, 2, $this->id(10), hash('sha256', 'intent'), [
            'ownerId' => $this->id(20),
            'contentChecksum' => $command->contentChecksum,
            'storageKey' => 'owners/'.$this->id(20).'/assets/'.$command->mediaId,
            'bytes' => 4,
        ]);

        return new DeterministicAttachReadyMediaAsset(
            new FixedAttachmentAssetStore($asset),
            $registry,
            new ExistingAttachmentPropertyCatalog,
            new InMemoryAttachmentIntentStore,
            new InlineAttachmentTransaction,
        );
    }

    private function command(): AttachReadyMediaAssetCommandV1
    {
        return new AttachReadyMediaAssetCommandV1(
            $this->id(5),
            hash('sha256', 'attachment-intent'),
            $this->id(2),
            $this->id(1),
            $this->id(3),
            hash('sha256', 'blob'),
            1,
            'Vue principale',
            'owner',
            0,
            '2026-08-10T10:01:00+00:00',
        );
    }

    private function id(int $suffix): string
    {
        return sprintf('66000000-0000-4000-8000-%012d', $suffix);
    }
}

final readonly class FixedAttachmentAssetStore implements MediaAssetStore
{
    public function __construct(private MediaAssetState $asset) {}

    public function read(string $assetId): ?MediaAssetState
    {
        return $assetId === $this->asset->id ? $this->asset : null;
    }

    public function save(MediaAssetState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        return MediaIngestionPersistenceWriteResult::Rejected;
    }
}

final class InMemoryAttachmentIntentStore implements MediaAttachmentIntentStore
{
    /** @var array<string, MediaAttachmentIntent> */
    private array $intents = [];

    public function find(string $intentId): ?MediaAttachmentIntent
    {
        return $this->intents[$intentId] ?? null;
    }

    public function reserve(MediaAttachmentIntent $intent): bool
    {
        if (isset($this->intents[$intent->intentId])) {
            return false;
        }
        $this->intents[$intent->intentId] = $intent;

        return true;
    }

    public function markApplied(string $intentId, int $aggregateVersion): void
    {
        $intent = $this->intents[$intentId];
        $this->intents[$intentId] = new MediaAttachmentIntent($intent->intentId, $intent->checksum, $intent->collectionId, $intent->propertyId, $intent->mediaId, $aggregateVersion);
    }
}

final readonly class ExistingAttachmentPropertyCatalog implements PropertyCatalog
{
    public function exists(PropertyId $propertyId): bool
    {
        return true;
    }
}

final readonly class InlineAttachmentTransaction implements MediaAttachmentTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
