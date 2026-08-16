<?php

namespace Tests\PostgreSQL\SearchDecisionMaterialization;

use App\Infrastructure\SearchDecisionMaterialization\PostgreSqlPublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadStatus;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchDecisionMaterializerV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchDecisionIdentityV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationStatus;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchFacetPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchProjectionPolicy;
use Appart\Modules\SearchDiscovery\Domain\Policy\SearchVisibilityPolicy;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionReader;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchDecisionMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicSearchDecisionMaterializationTest extends TestCase
{
    private const string LISTING = '979cd5aa-ced1-48a1-8adf-8b29c843a0c2';

    private const string PROPERTY = '5797a9b5-088d-43ea-8854-cac441946f6b';

    private const string REVISION = 'e40a5c74-d8cd-549b-8793-a8ea715de533';

    private const string PROMOTION = 'ef9e24b4-c42e-4221-8fe0-3f2e52cec2e6';

    private const string COLLECTION = '71fae610-6d12-5ad2-9c13-572c8eb1658c';

    private const string MEDIA = '1586b2bc-48ab-57b7-948a-9d9a19813ca8';

    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->seedPublishedSources();
    }

    public function test_real_sources_materialize_replay_and_advance_a_single_decision(): void
    {
        $mapper = new SearchDecisionMapper;
        $reader = new PostgreSqlSearchDecisionReader($this->connection, $mapper);
        $materializer = new DeterministicPublicSearchDecisionMaterializerV1(
            new PostgreSqlPublicSearchMaterializationSourceReaderV1($this->connection),
            new DeterministicPublicSearchRankingPolicyV1,
            new SearchProjectionPolicy(new SearchVisibilityPolicy, new SearchFacetPolicy),
            new PublicSearchDecisionIdentityV1,
            $reader,
            new PostgreSqlSearchDecisionWriter($this->connection, $mapper),
        );
        $id = ListingId::fromString(self::LISTING);

        $first = $materializer->materialize($id);
        self::assertSame(PublicSearchMaterializationStatus::Applied, $first->status);
        self::assertSame('20d5ab5a-45ac-5a5e-9f15-d1357e9105a9', $first->decision?->decisionId->value);
        self::assertSame(1, $first->decision?->version);
        self::assertSame(0, $first->decision?->projection->rank->value);
        self::assertSame([], $first->decision?->projection->facets);
        self::assertSame(SearchDecisionReadStatus::Found, $reader->readByListing($id)->status);

        self::assertSame(PublicSearchMaterializationStatus::AlreadyApplied, $materializer->materialize($id)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.public_search_decisions')->fetchColumn());

        $this->connection->exec("UPDATE media.media_collections SET version=2,last_changed_at=TIMESTAMPTZ '2026-08-15 08:01:01+00' WHERE id='".self::COLLECTION."'");
        $newer = $materializer->materialize($id);
        self::assertSame(PublicSearchMaterializationStatus::Applied, $newer->status);
        self::assertSame(2, $newer->decision?->version);
        self::assertSame('20d5ab5a-45ac-5a5e-9f15-d1357e9105a9', $newer->decision?->decisionId->value);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.public_search_decisions')->fetchColumn());
    }

    private function seedPublishedSources(): void
    {
        $this->connection->exec("INSERT INTO real_estate_catalog.properties(id,reference,type,surface,rooms,bathrooms,construction_year,status,last_changed_at,last_changed_at_offset,version) VALUES ('".self::PROPERTY."','RC2-SEARCH','apartment',90,3,2,2020,'active',TIMESTAMPTZ '2026-08-15 07:16:27+00',0,0)");
        $this->connection->exec("INSERT INTO listing_lifecycle.listings(id,property_id,status,last_changed_at,last_changed_at_offset,expiration_date,expiration_date_offset,version) VALUES ('".self::LISTING."','".self::PROPERTY."','published',TIMESTAMPTZ '2026-08-15 07:22:24+00',0,NULL,NULL,3)");
        $this->connection->exec("INSERT INTO listing_lifecycle.listing_revisions(listing_id,sequence,revision_id,previous_status,status,actor_id,trigger,reason,origin,occurred_at,occurred_at_offset) VALUES ('".self::LISTING."',4,'".self::REVISION."','under_review','published','reviewer','approve','approved','moderation',TIMESTAMPTZ '2026-08-15 07:22:24+00',0)");
        $this->connection->exec("INSERT INTO real_estate_catalog.property_promotion_commands(command_id,checksum,property_id,owner_account_id,authoring_version,occurred_at,result) VALUES ('".self::PROMOTION."','".str_repeat('a', 64)."','".self::PROPERTY."','11111111-1111-4111-8111-111111111111',1,TIMESTAMPTZ '2026-08-15 07:16:27+00','applied')");
        $this->connection->exec("INSERT INTO media.media_collections(id,property_id,last_changed_at,last_changed_at_offset,version) VALUES ('".self::COLLECTION."','".self::PROPERTY."',TIMESTAMPTZ '2026-08-15 07:01:01+00',0,1)");
        $this->connection->exec("INSERT INTO media.media_id_reservations(media_id,collection_id) VALUES ('".self::MEDIA."','".self::COLLECTION."')");
        $this->connection->exec("INSERT INTO media.media_items(media_id,collection_id,type,checksum,media_order,caption,source,status,is_primary,added_at,added_at_offset,removed_at,removed_at_offset,archived_at,archived_at_offset) VALUES ('".self::MEDIA."','".self::COLLECTION."','image','".str_repeat('b', 64)."',1,NULL,'owner','active',true,TIMESTAMPTZ '2026-08-15 07:01:01+00',0,NULL,NULL,NULL,NULL)");
    }
}
