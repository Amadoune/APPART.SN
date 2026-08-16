<?php

namespace Tests\PostgreSQL\AuthoringDraftResume;

use App\Application\AuthoringDraftResume\AuthoringDraftResumeStatus;
use App\Application\AuthoringDraftResume\Contract\AuthoringDraftResumeReaderV1;
use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPortfolioItem;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use DateTimeImmutable;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlAuthoringDraftResumeTest extends TestCase
{
    private const string OWNER = '83000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '83000000-0000-4000-8000-000000000002';

    private const string LISTING = '83000000-0000-4000-8000-000000000003';

    private const string COUNTRY = '83000000-0000-4000-8000-000000000010';

    private const string REGION = '83000000-0000-4000-8000-000000000011';

    private const string CITY = '83000000-0000-4000-8000-000000000012';

    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->app->instance(PDO::class, $this->connection);
    }

    public function test_real_persisted_draft_resumes_without_any_mutation(): void
    {
        $at = new DateTimeImmutable('2026-08-15T10:00:00+00:00');
        $places = $this->app->make(PlaceRegistry::class);
        $country = Place::create(PlaceId::fromString(self::COUNTRY), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $at);
        $region = Place::create(PlaceId::fromString(self::REGION), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $at, $country);
        $city = Place::create(PlaceId::fromString(self::CITY), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKR-CITY'), PlaceType::City, CountryCode::fromString('SN'), $at, $region);
        $places->add($country);
        $places->add($region);
        $places->add($city);

        $this->app->make(PropertyAuthoringStore::class)->save(new PropertyAuthoringState(
            self::PROPERTY, self::OWNER, 1, $this->id(20), str_repeat('a', 64), 'apartment', 'Dakar', 'Dakar',
            'RC2-RESUME', 120, 4, 2, 2020, self::CITY, 'Ngor Village', $this->id(21),
        ), 0);
        $this->app->make(ListingDraftStore::class)->save(new ListingDraftState(
            self::LISTING, self::PROPERTY, 'Appartement Dakar', 'Description réelle', 'sale', 25000000, 'XOF', null, null,
            'platform', 1, $this->id(22), str_repeat('b', 64),
        ), 0);
        $this->app->make(ListingOwnershipStore::class)->save(new ListingOwnershipState(
            self::LISTING, self::PROPERTY, self::OWNER, [], 1, $this->id(23), str_repeat('c', 64),
        ), 0);
        $this->app->make(AuthoringPortfolioStore::class)->project(new AuthoringPortfolioItem(
            self::OWNER, self::LISTING, self::PROPERTY, 'OWNER', 1, 1, 'COMPLETE', 1,
        ));
        $this->insertAggregate($at);
        $this->app->make(ListingPublicationWorkflowStore::class)->initialize(ListingId::fromString(self::LISTING), ListingPublicationState::Draft);

        $this->insertMedia($at);

        $before = $this->fingerprint();
        $result = $this->app->make(AuthoringDraftResumeReaderV1::class)->read(self::OWNER, self::LISTING);
        $after = $this->fingerprint();

        self::assertSame(AuthoringDraftResumeStatus::Available, $result->status);
        self::assertSame(self::PROPERTY, $result->snapshot['propertyId']);
        self::assertSame(self::LISTING, $result->snapshot['listingId']);
        self::assertSame(1, $result->snapshot['expectedAuthoringVersion']);
        self::assertSame(1, $result->snapshot['expectedVersion']);
        self::assertSame(self::CITY, $result->snapshot['geography']['geographicPlaceId']);
        self::assertCount(1, $result->snapshot['media']['items']);
        self::assertSame($before, $after);
    }

    /** @return array<string, array{count: int, checksum: ?string}> */
    private function fingerprint(): array
    {
        $tables = ['real_estate_catalog_authoring.property_authoring', 'listing_authoring.drafts', 'listing_authoring.ownerships', 'listing_authoring.portfolio_items', 'listing_lifecycle.listings', 'listing_lifecycle.publication_workflow_transitions', 'media.media_collections', 'media.media_items'];
        $result = [];
        foreach ($tables as $table) {
            $row = $this->connection->query("SELECT count(*)::int AS count, md5(string_agg(row_to_json(t)::text, '|' ORDER BY row_to_json(t)::text)) AS checksum FROM {$table} t")->fetch(PDO::FETCH_ASSOC);
            $result[$table] = ['count' => (int) $row['count'], 'checksum' => isset($row['checksum']) ? (string) $row['checksum'] : null];
        }

        return $result;
    }

    private function collectionId(): string
    {
        $hash = hash('sha256', 'collection:'.self::PROPERTY);

        return sprintf('%s-%s-5%s-%s%s-%s', substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 13, 3), dechex((hexdec($hash[16]) & 0x3) | 0x8), substr($hash, 17, 3), substr($hash, 20, 12));
    }

    private function insertAggregate(DateTimeImmutable $at): void
    {
        $root = $this->connection->prepare("INSERT INTO listing_lifecycle.listings(id,property_id,status,last_changed_at,last_changed_at_offset,version) VALUES(:listing,:property,'draft',:at,0,0)");
        $root->execute(['listing' => self::LISTING, 'property' => self::PROPERTY, 'at' => $at->format('Y-m-d H:i:s.uP')]);
        $revision = $this->connection->prepare("INSERT INTO listing_lifecycle.listing_revisions(listing_id,sequence,revision_id,previous_status,status,actor_id,trigger,reason,origin,occurred_at,occurred_at_offset) VALUES(:listing,1,:revision,NULL,'draft',:actor,'draft_started','Initial authoring draft.','advertiser',:at,0)");
        $revision->execute(['listing' => self::LISTING, 'revision' => $this->id(24), 'actor' => self::OWNER, 'at' => $at->format('Y-m-d H:i:s.uP')]);
    }

    private function insertMedia(DateTimeImmutable $at): void
    {
        $collection = $this->collectionId();
        $media = $this->id(31);
        $timestamp = $at->format('Y-m-d H:i:s.uP');
        $root = $this->connection->prepare('INSERT INTO media.media_collections(id,property_id,last_changed_at,last_changed_at_offset,version) VALUES(:id,:property,:at,0,1)');
        $root->execute(['id' => $collection, 'property' => self::PROPERTY, 'at' => $timestamp]);
        $reservation = $this->connection->prepare('INSERT INTO media.media_id_reservations(media_id,collection_id) VALUES(:media,:collection)');
        $reservation->execute(['media' => $media, 'collection' => $collection]);
        $item = $this->connection->prepare("INSERT INTO media.media_items(media_id,collection_id,type,checksum,media_order,caption,source,status,is_primary,added_at,added_at_offset) VALUES(:media,:collection,'image',:checksum,1,'Façade','owner','active',true,:at,0)");
        $item->execute(['media' => $media, 'collection' => $collection, 'checksum' => str_repeat('d', 64), 'at' => $timestamp]);
    }

    private function id(int $suffix): string
    {
        return sprintf('83000000-0000-4000-8000-%012d', $suffix);
    }
}
