<?php

namespace Tests\Unit\PublicProjectionUpdater;

use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use App\Application\PublicProjectionUpdater\Contract\PublicListingProjectionSource;
use App\Application\PublicProjectionUpdater\PublicListingProjectionSources;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdater;
use App\Projections\SearchListingProjectionBuilder;
use App\Projections\SeoListingProjectionBuilder;
use App\ReadModels\PublicListingReadModelBuilder;
use Appart\Modules\ContentSeo\Domain\Model\BreadcrumbItem;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicMediaSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\ListingSeoDecisionPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as SeoListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PublicMediaUrl;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId as ListingPropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\PublicProjectionStore\Support\FakePublicListingProjectionStore;

final class PublicListingProjectionUpdaterTest extends TestCase
{
    private const LISTING_ID = '71000000-0000-4000-8000-000000000001';

    private const PROPERTY_ID = '70000000-0000-4000-8000-000000000001';

    public function test_complete_reconstruction_is_applied_and_is_deterministic(): void
    {
        $writer = new ScriptedWriter(PublicProjectionWriteResult::Applied);
        $first = $this->updater($this->sources(), $writer)->update(self::LISTING_ID);
        $firstRecord = $writer->record;
        $second = $this->updater($this->sources(), $writer)->update(self::LISTING_ID);

        self::assertSame(PublicListingProjectionUpdateOutcome::Applied, $first->outcome);
        self::assertSame(PublicProjectionPromotionReadiness::Ready, $first->readiness);
        self::assertNotNull($firstRecord);
        self::assertEquals($firstRecord, $writer->record);
        self::assertSame(PublicListingProjectionUpdateOutcome::Applied, $second->outcome);
        self::assertSame('annonces/appartement-moderne-dakar', $writer->record?->canonicalPath);
    }

    public function test_absent_source_is_explicit_and_never_calls_writer(): void
    {
        $writer = new ScriptedWriter(PublicProjectionWriteResult::Applied);
        $result = $this->updater(null, $writer)->update(self::LISTING_ID);

        self::assertSame(PublicListingProjectionUpdateOutcome::SourceUnavailable, $result->outcome);
        self::assertSame(0, $writer->calls);
    }

    public function test_repeating_the_same_reconstruction_is_idempotent_on_the_contract_fake(): void
    {
        $generation = new PublicProjectionGeneration(
            PublicProjectionGenerationId::fromString('90000000-0000-4000-8000-000000000001'),
            PublicProjectionGenerationState::Active,
        );
        $store = new FakePublicListingProjectionStore($generation);
        $updater = $this->updater($this->sources(), $store);

        self::assertSame(PublicListingProjectionUpdateOutcome::Applied, $updater->update(self::LISTING_ID)->outcome);
        self::assertSame(PublicListingProjectionUpdateOutcome::AlreadyApplied, $updater->update(self::LISTING_ID)->outcome);
    }

    #[DataProvider('incompleteSources')]
    public function test_missing_public_source_version_blocks_promotion(bool $geography, bool $media, PublicProjectionPromotionReadiness $readiness): void
    {
        $writer = new ScriptedWriter(PublicProjectionWriteResult::Applied);
        $result = $this->updater($this->sources($geography, $media), $writer)->update(self::LISTING_ID);

        self::assertSame(PublicListingProjectionUpdateOutcome::PromotionNotReady, $result->outcome);
        self::assertSame($readiness, $result->readiness);
        self::assertSame(0, $writer->calls);
    }

    public static function incompleteSources(): iterable
    {
        yield 'geography' => [false, true, PublicProjectionPromotionReadiness::MissingPublicGeographyVersion];
        yield 'public media' => [true, false, PublicProjectionPromotionReadiness::MissingPublicMediaVersion];
        yield 'both' => [false, false, PublicProjectionPromotionReadiness::MissingPublicGeographyAndMediaVersions];
    }

    #[DataProvider('writerResults')]
    public function test_every_writer_result_has_an_explicit_outcome(PublicProjectionWriteResult $writeResult, PublicListingProjectionUpdateOutcome $outcome): void
    {
        $result = $this->updater($this->sources(), new ScriptedWriter($writeResult))->update(self::LISTING_ID);

        self::assertSame($outcome, $result->outcome);
        self::assertSame($writeResult, $result->writeResult);
    }

    public static function writerResults(): iterable
    {
        foreach (PublicProjectionWriteResult::cases() as $result) {
            yield $result->value => [$result, PublicListingProjectionUpdateOutcome::from($result->value)];
        }
    }

    private function updater(?PublicListingProjectionSources $sources, PublicListingProjectionWriter $writer): PublicListingProjectionUpdater
    {
        $source = new class($sources) implements PublicListingProjectionSource
        {
            public function __construct(private readonly ?PublicListingProjectionSources $sources) {}

            public function findByListingId(string $listingId): ?PublicListingProjectionSources
            {
                return $this->sources;
            }
        };

        return new PublicListingProjectionUpdater($source, new SearchListingProjectionBuilder, new ListingSeoDecisionPolicy(new CanonicalPolicy, new CanonicalHistoryPolicy), new SeoListingProjectionBuilder, new PublicListingReadModelBuilder, $writer);
    }

    private function sources(bool $geography = true, bool $publicMedia = true): PublicListingProjectionSources
    {
        $id = SeoListingId::fromString(self::LISTING_ID);

        return new PublicListingProjectionSources(
            $this->property(), $this->media(), $this->listing(),
            new ListingSeoSource($id, ListingSeoState::Published, 'Appartement moderne a Dakar', 'Decouvrez cet appartement moderne, lumineux et bien situe au coeur de Dakar pour votre prochain logement.', 'annonces/appartement-moderne-dakar', $this->revision(SeoSourceKind::Listing), $this->at(3), $this->at(120)),
            new SearchSeoSource($id, SearchSeoState::Public, $this->revision(SeoSourceKind::Search)),
            new PropertySeoSource($id, PropertySeoState::Available, 'Appartement', 'Dakar', $this->revision(SeoSourceKind::Property)),
            $geography ? new PublicGeographySeoSource($id, 'Dakar', [new BreadcrumbItem('Accueil', CanonicalUrl::fromString('https://appart.sn/accueil'))]) : null,
            $publicMedia ? new PublicMediaSeoSource($id, PublicMediaUrl::fromString('https://media.appart.sn/listings/primary.webp')) : null,
            [], $this->at(4), 4, 4, $geography ? 1 : null, $publicMedia ? 1 : null,
            PublicProjectionGenerationId::fromString('90000000-0000-4000-8000-000000000001'),
        );
    }

    private function property(): Property
    {
        return Property::register(PropertyId::fromString(self::PROPERTY_ID), PropertyReference::fromString('UPDATER-PROPERTY-001'), PropertyType::Apartment, SurfaceArea::fromSquareMeters(120), RoomCount::fromInt(5), BathroomCount::fromInt(2), ConstructionYear::fromInt(2020), new Address(AddressId::fromString('70000000-0000-4000-8000-000000000010'), GeographicPlaceId::fromString('place:dakar:plateau'), AddressLine::fromString('Adresse source')), BusinessYear::fromInt(2026), new PropertyTypePolicy, $this->at(0));
    }

    private function media(): MediaCollection
    {
        $media = MediaCollection::create(MediaCollectionId::fromString('72000000-0000-4000-8000-000000000001'), MediaPropertyId::fromString(self::PROPERTY_ID), $this->at(0));
        $media->add(MediaId::fromString('72000000-0000-4000-8000-000000000101'), MediaType::Image, MediaChecksum::fromSha256(str_repeat('a', 64)), MediaOrder::fromInt(1), null, MediaSource::Owner, $this->at(1));

        return $media;
    }

    private function listing(): Listing
    {
        $policy = new ListingTransitionPolicy;
        $listing = Listing::createDraft(ListingId::fromString(self::LISTING_ID), ListingPropertyId::fromString(self::PROPERTY_ID), $this->revisionId(1), $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0), $policy, PropertyAvailability::Eligible);
        $listing->submit($this->revisionId(2), $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 1), $policy, PropertyAvailability::Eligible);
        $listing->sendToReview($this->revisionId(3), $this->evidence(TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation, 2), $policy, PropertyAvailability::Eligible);
        $listing->publish($this->revisionId(4), ExpirationDate::fromDateTime($this->at(120)), $this->evidence(TransitionTrigger::FavorableReview, TransitionOrigin::Moderation, 3), $policy, PropertyAvailability::Eligible);

        return $listing;
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin, int $minute): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor:projection-updater'), $trigger, TransitionReason::fromString('Projection updater fixture'), $origin, $this->at($minute));
    }

    private function revisionId(int $suffix): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('71000000-0000-4000-8000-%012d', $suffix));
    }

    private function revision(SeoSourceKind $kind): SeoSourceRevision
    {
        $offset = match ($kind) {
            SeoSourceKind::Listing => 1, SeoSourceKind::Search => 2, SeoSourceKind::Property => 3
        };

        return SeoSourceRevision::create($kind, 4, sprintf('80000000-0000-4000-8000-%012d', $offset), '81000000-0000-4000-8000-000000000004', $this->at(4));
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-18T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}

final class ScriptedWriter implements PublicListingProjectionWriter
{
    public int $calls = 0;

    public ?PublicListingProjectionRecord $record = null;

    public function __construct(private readonly PublicProjectionWriteResult $result) {}

    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        $this->calls++;
        $this->record = $record;

        return $this->result;
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        return $this->result;
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        return $this->result;
    }

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        return $this->result;
    }
}
