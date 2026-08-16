<?php

namespace Tests\PostgreSQL\PublicMediaMaterialization;

use App\Application\PublicMediaMaterialization\DeterministicPublicMediaDecisionMaterializerV2;
use App\Application\PublicMediaMaterialization\PublicMediaMaterializationStatus;
use App\Infrastructure\PublicMediaMaterialization\PostgreSqlPublicMediaOwnerSourceReaderV2;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaMapper;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaReader;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaWriter;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicMediaMaterializationV2Test extends TestCase
{
    private const string LISTING = '979cd5aa-ced1-48a1-8adf-8b29c843a0c2';

    private const string PROPERTY = '5797a9b5-088d-43ea-8854-cac441946f6b';

    private const string COLLECTION = '71fae610-6d12-5ad2-9c13-572c8eb1658c';

    private const string MEDIA = '1586b2bc-48ab-57b7-948a-9d9a19813ca8';

    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->seedReadyPublishedMedia();
    }

    public function test_applied_found_replay_revision_refresh_and_empty_readiness(): void
    {
        $mapper = new PostgreSqlPublicMediaMapper('https://public.example.test');
        $reader = new PostgreSqlPublicMediaReader($this->connection, $mapper);
        $materializer = new DeterministicPublicMediaDecisionMaterializerV2(
            new PostgreSqlPublicMediaOwnerSourceReaderV2($this->connection),
            $reader,
            new PostgreSqlPublicMediaWriter($this->connection, $mapper),
        );
        $listingId = ListingId::fromString(self::LISTING);

        $first = $materializer->materialize($listingId);
        self::assertSame(PublicMediaMaterializationStatus::Applied, $first->status);
        self::assertSame(2, $first->decision?->schemaVersion);
        self::assertSame(1, $first->decision?->revision->version->value);
        self::assertSame('/media/'.self::MEDIA.'/revisions/2', $first->decision?->itemsV2[0]->publicLocator);
        self::assertSame(1, $first->decision?->itemsV2[0]->order);
        self::assertTrue($first->decision?->itemsV2[0]->primary);
        $stored = $this->connection->query('SELECT media_collection_id,version,causation_key,revision_checksum,payload::text AS payload,payload_checksum FROM public_media.decisions')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($stored);
        try {
            self::assertSame(2, $mapper->toDecision($stored)->schemaVersion);
        } catch (\Throwable $error) {
            self::fail($error->getPrevious()?->getMessage() ?? $error->getMessage());
        }
        self::assertSame(PublicMediaMaterializationStatus::AlreadyApplied, $materializer->materialize($listingId)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM public_media.decisions')->fetchColumn());

        $this->connection->exec("UPDATE media_ingestion.assets SET version=3,payload=jsonb_set(payload,'{contentChecksum}','\"".str_repeat('c', 64)."\"') WHERE aggregate_id='".self::MEDIA."'");
        $this->connection->exec("UPDATE media.media_collections SET version=2 WHERE id='".self::COLLECTION."'");
        $updated = $materializer->materialize($listingId);
        self::assertSame(PublicMediaMaterializationStatus::Applied, $updated->status);
        self::assertSame(2, $updated->decision?->revision->version->value);
        self::assertSame('/media/'.self::MEDIA.'/revisions/3', $updated->decision?->itemsV2[0]->publicLocator);

        $this->connection->exec("UPDATE media.media_items SET status='archived',is_primary=false,archived_at=TIMESTAMPTZ '2026-08-15 10:00:00+00' WHERE media_id='".self::MEDIA."'");
        self::assertSame(PublicMediaMaterializationStatus::SourceNotReady, $materializer->materialize($listingId)->status);
    }

    private function seedReadyPublishedMedia(): void
    {
        $owner = '11111111-1111-4111-8111-111111111111';
        $this->connection->exec("INSERT INTO real_estate_catalog.properties(id,reference,type,surface,rooms,bathrooms,construction_year,status,last_changed_at,last_changed_at_offset,version) VALUES ('".self::PROPERTY."','PM-V2','apartment',90,3,2,2020,'active',TIMESTAMPTZ '2026-08-15 07:00:00+00',0,1)");
        $this->connection->exec("INSERT INTO listing_lifecycle.listings(id,property_id,status,last_changed_at,last_changed_at_offset,version) VALUES ('".self::LISTING."','".self::PROPERTY."','published',TIMESTAMPTZ '2026-08-15 07:00:00+00',0,3)");
        $this->connection->exec("INSERT INTO media.media_collections(id,property_id,last_changed_at,last_changed_at_offset,version) VALUES ('".self::COLLECTION."','".self::PROPERTY."',TIMESTAMPTZ '2026-08-15 07:00:00+00',0,1)");
        $this->connection->exec("INSERT INTO media.media_id_reservations(media_id,collection_id) VALUES ('".self::MEDIA."','".self::COLLECTION."')");
        $this->connection->exec("INSERT INTO media.media_items(media_id,collection_id,type,checksum,media_order,caption,source,status,is_primary,added_at,added_at_offset) VALUES ('".self::MEDIA."','".self::COLLECTION."','image','".str_repeat('a', 64)."',1,NULL,'owner','active',true,TIMESTAMPTZ '2026-08-15 07:00:00+00',0)");
        $payload = json_encode(['ownerId' => $owner, 'contentChecksum' => str_repeat('a', 64), 'bytes' => 4, 'contentType' => 'image/jpeg'], JSON_THROW_ON_ERROR);
        $this->connection->exec("INSERT INTO media_ingestion.assets(aggregate_id,state,version,last_intent_id,last_intent_checksum,payload) VALUES ('".self::MEDIA."','ready',2,'22222222-2222-4222-8222-222222222222','".str_repeat('b', 64)."','".$payload."'::jsonb)");
        $this->connection->exec("INSERT INTO media.media_attachment_intents(operation,intent_id,checksum,collection_id,property_id,media_id,result,aggregate_version) VALUES ('AttachReadyMediaAssetV1','33333333-3333-4333-8333-333333333333','".str_repeat('d', 64)."','".self::COLLECTION."','".self::PROPERTY."','".self::MEDIA."','applied',1)");
    }
}
