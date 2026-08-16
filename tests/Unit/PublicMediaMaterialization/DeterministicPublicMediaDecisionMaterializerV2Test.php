<?php

namespace Tests\Unit\PublicMediaMaterialization;

use App\Application\PublicMediaMaterialization\Contract\PublicMediaOwnerSourceReaderV2;
use App\Application\PublicMediaMaterialization\DeterministicPublicMediaDecisionMaterializerV2;
use App\Application\PublicMediaMaterialization\PublicMediaMaterializationStatus;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerItemV2;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceResult;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceStatus;
use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceV2;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionWriter;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaReadResult;
use App\Application\PublicMediaSource\PublicMediaWriteResult;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PHPUnit\Framework\TestCase;

final class DeterministicPublicMediaDecisionMaterializerV2Test extends TestCase
{
    private const string LISTING = '979cd5aa-ced1-48a1-8adf-8b29c843a0c2';

    private const string COLLECTION = '71fae610-6d12-5ad2-9c13-572c8eb1658c';

    private const string MEDIA = '1586b2bc-48ab-57b7-948a-9d9a19813ca8';

    public function test_initial_v2_materialization_and_strict_replay_are_deterministic(): void
    {
        $source = new MutablePublicMediaOwnerSourceReader($this->readySource());
        $store = new InMemoryPublicMediaDecisionStore;
        $materializer = new DeterministicPublicMediaDecisionMaterializerV2($source, $store, $store);

        $first = $materializer->materialize(ListingId::fromString(self::LISTING));
        self::assertSame(PublicMediaMaterializationStatus::Applied, $first->status);
        self::assertSame(2, $first->decision?->schemaVersion);
        self::assertSame(1, $first->decision?->revision->version->value);
        self::assertSame('/media/'.self::MEDIA.'/revisions/2', $first->decision?->itemsV2[0]->publicLocator);
        self::assertSame(1, $first->decision?->itemsV2[0]->order);
        self::assertTrue($first->decision?->itemsV2[0]->primary);
        self::assertStringNotContainsString('storageKey', $first->decision?->canonicalPayload() ?? '');
        self::assertStringNotContainsString('https://', $first->decision?->canonicalPayload() ?? '');

        $replay = $materializer->materialize(ListingId::fromString(self::LISTING));
        self::assertSame(PublicMediaMaterializationStatus::AlreadyApplied, $replay->status);
        self::assertSame(1, $store->writes);
    }

    public function test_asset_revision_change_advances_version_and_locator(): void
    {
        $source = new MutablePublicMediaOwnerSourceReader($this->readySource());
        $store = new InMemoryPublicMediaDecisionStore;
        $materializer = new DeterministicPublicMediaDecisionMaterializerV2($source, $store, $store);
        self::assertSame(PublicMediaMaterializationStatus::Applied, $materializer->materialize(ListingId::fromString(self::LISTING))->status);

        $source->result = $this->readySource(assetVersion: 3, collectionVersion: 2);
        $updated = $materializer->materialize(ListingId::fromString(self::LISTING));
        self::assertSame(PublicMediaMaterializationStatus::Applied, $updated->status);
        self::assertSame(2, $updated->decision?->revision->version->value);
        self::assertSame('/media/'.self::MEDIA.'/revisions/3', $updated->decision?->itemsV2[0]->publicLocator);
    }

    public function test_empty_or_unready_media_is_source_not_ready(): void
    {
        $source = new MutablePublicMediaOwnerSourceReader(new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::NotReady));
        $store = new InMemoryPublicMediaDecisionStore;
        $result = (new DeterministicPublicMediaDecisionMaterializerV2($source, $store, $store))->materialize(ListingId::fromString(self::LISTING));

        self::assertSame(PublicMediaMaterializationStatus::SourceNotReady, $result->status);
        self::assertSame(0, $store->writes);
    }

    private function readySource(int $assetVersion = 2, int $collectionVersion = 1): PublicMediaOwnerSourceResult
    {
        return new PublicMediaOwnerSourceResult(PublicMediaOwnerSourceStatus::Ready, new PublicMediaOwnerSourceV2(
            self::LISTING,
            3,
            'published',
            self::COLLECTION,
            $collectionVersion,
            [new PublicMediaOwnerItemV2(self::MEDIA, 1, true, $assetVersion, 'ready', str_repeat('a', 64), 1, str_repeat('b', 64))],
        ));
    }
}

final class MutablePublicMediaOwnerSourceReader implements PublicMediaOwnerSourceReaderV2
{
    public function __construct(public PublicMediaOwnerSourceResult $result) {}

    public function read(ListingId $listingId): PublicMediaOwnerSourceResult
    {
        return $this->result;
    }
}

final class InMemoryPublicMediaDecisionStore implements PublicMediaDecisionReader, PublicMediaDecisionWriter
{
    public ?PublicMediaDecision $decision = null;

    public int $writes = 0;

    public function read(string $mediaCollectionId): PublicMediaReadResult
    {
        return $this->decision === null ? PublicMediaReadResult::missing($mediaCollectionId) : PublicMediaReadResult::found($mediaCollectionId, $this->decision);
    }

    public function store(PublicMediaDecision $decision): PublicMediaWriteResult
    {
        $this->writes++;
        $this->decision = $decision;

        return PublicMediaWriteResult::Applied;
    }
}
