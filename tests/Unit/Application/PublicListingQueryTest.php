<?php

namespace Tests\Unit\Application;

use App\Application\Contract\PublicListingQuery;
use App\ReadModels\PublicListingReadModel;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\Support\InMemoryPublicListingQuery;

final class PublicListingQueryTest extends TestCase
{
    public function test_current_canonical_path_resolves_the_public_read_model(): void
    {
        $expected = $this->model();
        $query = $this->query($expected);

        $actual = $query->findByCanonicalPath('annonces/appartement-moderne-dakar');

        self::assertEquals($expected, $actual);
        self::assertNotSame($expected, $actual);
    }

    public function test_unknown_path_is_absent(): void
    {
        self::assertNull($this->query($this->model())->findByCanonicalPath('annonces/inconnue'));
    }

    public function test_historical_canonical_path_is_not_served_as_the_current_identity(): void
    {
        self::assertNull($this->query($this->model())->findByCanonicalPath('annonces/ancienne-url'));
    }

    public function test_listing_id_is_never_used_as_a_public_identity_fallback(): void
    {
        $model = $this->model();

        self::assertNull($this->query($model)->findByCanonicalPath($model->listingId));
    }

    public function test_lookup_is_exact_and_deterministic(): void
    {
        $query = $this->query($this->model());

        self::assertNull($query->findByCanonicalPath('annonces/appartement-moderne-dakar/'));
        self::assertEquals(
            $query->findByCanonicalPath('annonces/appartement-moderne-dakar'),
            $query->findByCanonicalPath('annonces/appartement-moderne-dakar'),
        );
    }

    private function query(PublicListingReadModel $model): PublicListingQuery
    {
        return new InMemoryPublicListingQuery([$model]);
    }

    private function model(): PublicListingReadModel
    {
        $publishedAt = new DateTimeImmutable('2026-07-18T10:01:00+00:00');
        $expiresAt = new DateTimeImmutable('2026-11-15T10:00:00+00:00');
        $canonical = 'https://appart.sn/annonces/appartement-moderne-dakar';

        return new PublicListingReadModel(
            listingId: '91000000-0000-4000-8000-000000000001',
            propertyId: '92000000-0000-4000-8000-000000000001',
            mediaCollectionId: '93000000-0000-4000-8000-000000000001',
            headline: 'Appartement moderne a Dakar | APPART.SN',
            description: 'Decouvrez cet appartement moderne, lumineux et bien situe au coeur de Dakar pour votre prochain logement.',
            propertyType: 'apartment',
            surfaceSquareMeters: 120,
            roomCount: 5,
            geographicPlaceId: 'place:dakar:plateau',
            primaryMediaId: '93000000-0000-4000-8000-000000000101',
            publicMediaUrl: 'https://media.appart.sn/listings/primary.webp',
            listingStatus: 'published',
            publishedAt: $publishedAt,
            expiresAt: $expiresAt,
            canonicalUrl: $canonical,
            canonicalHistory: [
                ['url' => 'https://appart.sn/annonces/ancienne-url', 'disposition' => 'reserved_for_redirect', 'effectiveAt' => $publishedAt, 'replacedAt' => $publishedAt, 'redirectTarget' => $canonical],
                ['url' => $canonical, 'disposition' => 'current', 'effectiveAt' => $publishedAt, 'replacedAt' => null, 'redirectTarget' => null],
            ],
            indexability: 'indexable',
            robots: 'index_follow',
            htmlRobotsDirective: 'index, follow',
            breadcrumb: [['label' => 'Accueil', 'url' => 'https://appart.sn/accueil']],
            structuredData: ['type' => 'real_estate_listing', 'facts' => ['addressLocality' => 'Dakar', 'category' => 'Appartement', 'name' => 'Appartement moderne a Dakar | APPART.SN', 'url' => $canonical]],
            publicJsonLd: '{"@context":"https://schema.org","@type":"RealEstateListing"}',
            decidedAt: new DateTimeImmutable('2026-07-18T10:02:00+00:00'),
            expiredListingTreatment: 'not_applicable',
        );
    }
}
