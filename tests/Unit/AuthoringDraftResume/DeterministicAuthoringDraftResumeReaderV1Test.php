<?php

namespace Tests\Unit\AuthoringDraftResume;

use App\Application\AuthoringDraftResume\AuthoringDraftResumeStatus;
use App\Application\AuthoringDraftResume\DeterministicAuthoringDraftResumeReaderV1;
use App\Application\MediaAuthoringHttp\Contract\MediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpResult;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpStatus;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeReport;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
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
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeterministicAuthoringDraftResumeReaderV1Test extends TestCase
{
    private const string OWNER = '81000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '81000000-0000-4000-8000-000000000002';

    private const string LISTING = '81000000-0000-4000-8000-000000000003';

    private const string COUNTRY = '81000000-0000-4000-8000-000000000010';

    private const string REGION = '81000000-0000-4000-8000-000000000011';

    private const string CITY = '81000000-0000-4000-8000-000000000012';

    public function test_available_resume_preserves_identities_versions_geography_media_and_step(): void
    {
        $reader = $this->reader(MediaAuthoringHttpStatus::Available);
        $result = $reader->read(self::OWNER, self::LISTING);

        self::assertSame(AuthoringDraftResumeStatus::Available, $result->status);
        self::assertSame(self::PROPERTY, $result->snapshot['propertyId']);
        self::assertSame(self::LISTING, $result->snapshot['listingId']);
        self::assertSame(1, $result->snapshot['expectedAuthoringVersion']);
        self::assertSame(1, $result->snapshot['expectedVersion']);
        self::assertSame(6, $result->snapshot['step']);
        self::assertSame(self::CITY, $result->snapshot['geography']['geographicPlaceId']);
        self::assertSame('Dakar', $result->snapshot['geography']['label']);
        self::assertCount(1, $result->snapshot['media']['items']);
        self::assertArrayNotHasKey('revisionId', $result->snapshot);
    }

    public function test_portfolio_scope_refuses_an_arbitrary_listing_without_read_fallback(): void
    {
        $reader = $this->reader(MediaAuthoringHttpStatus::Available, []);

        self::assertSame(
            AuthoringDraftResumeStatus::NotFoundOrForbidden,
            $reader->read(self::OWNER, self::LISTING)->status,
        );
    }

    public function test_media_unavailability_is_reduced_fail_closed(): void
    {
        self::assertSame(
            AuthoringDraftResumeStatus::DependencyUnavailable,
            $this->reader(MediaAuthoringHttpStatus::Unavailable)->read(self::OWNER, self::LISTING)->status,
        );
    }

    public function test_result_catalog_is_closed(): void
    {
        self::assertSame([
            'available', 'not_found_or_forbidden', 'incomplete', 'state_conflict', 'corrupted', 'dependency_unavailable',
        ], array_column(AuthoringDraftResumeStatus::cases(), 'value'));
    }

    /** @param list<AuthoringPortfolioItem>|null $portfolioItems */
    private function reader(MediaAuthoringHttpStatus $mediaStatus, ?array $portfolioItems = null): DeterministicAuthoringDraftResumeReaderV1
    {
        $propertyStore = $this->createMock(PropertyAuthoringStore::class);
        $draftStore = $this->createMock(ListingDraftStore::class);
        $ownershipStore = $this->createMock(ListingOwnershipStore::class);
        $portfolio = $this->createMock(AuthoringPortfolioStore::class);
        $runtime = $this->createMock(PropertyListingAuthoringRuntimeV1::class);

        $runtime->method('inspect')->willReturn(new PropertyListingAuthoringRuntimeReport(PropertyListingAuthoringRuntimeStatus::Ready));
        $runtime->method('propertyAuthoring')->willReturn($propertyStore);
        $runtime->method('listingDraft')->willReturn($draftStore);
        $runtime->method('listingOwnership')->willReturn($ownershipStore);
        $runtime->method('authoringPortfolio')->willReturn($portfolio);

        $portfolio->method('listFor')->willReturn($portfolioItems ?? [new AuthoringPortfolioItem(self::OWNER, self::LISTING, self::PROPERTY, 'OWNER', 1, 1, 'COMPLETE', 1)]);
        $propertyStore->method('read')->willReturn(new PropertyAuthoringState(
            self::PROPERTY, self::OWNER, 1, $this->id(20), str_repeat('a', 64), 'apartment', 'Dakar', 'Dakar',
            'RC2-RESUME', 120, 4, 2, 2020, self::CITY, 'Ngor Village', $this->id(21),
        ));
        $draftStore->method('read')->willReturn(new ListingDraftState(
            self::LISTING, self::PROPERTY, 'Appartement Dakar', 'Description réelle', 'sale', 25000000, 'XOF', null, null,
            'platform', 1, $this->id(22), str_repeat('b', 64),
        ));
        $ownershipStore->method('read')->willReturn(new ListingOwnershipState(
            self::LISTING, self::PROPERTY, self::OWNER, [], 1, $this->id(23), str_repeat('c', 64),
        ));

        $listingRegistry = $this->createMock(ListingRegistry::class);
        $listingRegistry->method('find')->willReturn(Listing::reconstitute(
            ListingId::fromString(self::LISTING), PropertyId::fromString(self::PROPERTY), ListingStatus::Draft,
            new DateTimeImmutable('2026-08-15T10:00:00+00:00'), [], null, 0,
        ));
        $workflows = $this->createMock(ListingPublicationWorkflowStore::class);
        $workflows->method('read')->willReturn(ListingPublicationPersistenceReadResult::found(new ListingPublicationStoredState(
            ListingId::fromString(self::LISTING), ListingPublicationState::Draft, 1,
        )));

        $media = $this->createMock(MediaAuthoringHttpRuntime::class);
        $media->method('collection')->willReturn(new MediaAuthoringHttpResult($mediaStatus, $mediaStatus === MediaAuthoringHttpStatus::Available ? [
            'collectionId' => $this->id(30), 'version' => 1,
            'items' => [['mediaId' => $this->id(31), 'type' => 'image', 'checksum' => str_repeat('d', 64), 'order' => 1, 'caption' => 'Façade', 'status' => 'active', 'primary' => true]],
        ] : []));

        $country = Place::create(PlaceId::fromString(self::COUNTRY), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), new DateTimeImmutable('2026-01-01T00:00:00+00:00'));
        $region = Place::create(PlaceId::fromString(self::REGION), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), new DateTimeImmutable('2026-01-01T00:00:00+00:00'), $country);
        $city = Place::create(PlaceId::fromString(self::CITY), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKR-CITY'), PlaceType::City, CountryCode::fromString('SN'), new DateTimeImmutable('2026-01-01T00:00:00+00:00'), $region);
        $places = $this->createMock(PlaceRegistry::class);
        $places->method('find')->willReturnCallback(static fn (PlaceId $id): ?Place => match ($id->value) {
            self::COUNTRY => $country, self::REGION => $region, self::CITY => $city, default => null,
        });

        return new DeterministicAuthoringDraftResumeReaderV1($runtime, $listingRegistry, $workflows, $media, $places);
    }

    private function id(int $suffix): string
    {
        return sprintf('81000000-0000-4000-8000-%012d', $suffix);
    }
}
