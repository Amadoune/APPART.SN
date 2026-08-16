<?php

namespace Tests\Unit\PropertyAuthoringSourceCompleteness;

use App\Application\PropertyAuthoringGeographySelection\Contract\GeographySelectionReplayValidatorV1;
use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayResult;
use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayStatus;
use App\Application\PropertyAuthoringSourceCompleteness\DeterministicPropertyAuthoringStateEnricherV1;
use App\Application\PropertyAuthoringSourceCompleteness\PropertyAuthoringEnrichmentStatus;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringSourceCompleteness;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use PHPUnit\Framework\TestCase;

final class PropertyAuthoringSourceCompletenessTest extends TestCase
{
    private const string PROPERTY = 'a1000000-0000-4000-8000-000000000001';

    private const string OWNER = 'a1000000-0000-4000-8000-000000000002';

    private const string INTENT = 'a1000000-0000-4000-8000-000000000003';

    private const string PLACE = 'a1000000-0000-4000-8000-000000000004';

    private const string PARENT = 'a1000000-0000-4000-8000-000000000005';

    public function test_eight_sources_are_normalized_and_complete_without_domain_promotion(): void
    {
        $result = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, null, $this->facts());

        self::assertSame(PropertyAuthoringEnrichmentStatus::Validated, $result->status);
        self::assertSame('REF-001', $result->state?->propertyReference);
        self::assertSame(120, $result->state?->surfaceSquareMeters);
        self::assertSame(4, $result->state?->rooms);
        self::assertSame(2, $result->state?->bathrooms);
        self::assertSame(2020, $result->state?->constructionYear);
        self::assertSame(self::PLACE, $result->state?->geographicPlaceId);
        self::assertSame('12 avenue Cheikh Anta Diop', $result->state?->addressLine);
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', (string) $result->state?->addressIntentId);
        self::assertSame(PropertyAuthoringSourceCompleteness::CompleteForPromotion, $result->state?->completeness());
    }

    public function test_legacy_snapshot_reconstructs_with_null_sources_and_is_incomplete(): void
    {
        $state = (new PropertyAuthoringMapper)->toState([
            'property_id' => self::PROPERTY,
            'owner_account_id' => self::OWNER,
            'version' => 1,
            'last_intent_id' => self::INTENT,
            'last_intent_checksum' => str_repeat('a', 64),
            'property_type' => 'apartment',
            'city' => 'Dakar',
            'neighborhood' => 'Almadies',
        ]);

        self::assertNull($state->propertyReference);
        self::assertNull($state->geographicPlaceId);
        self::assertNull($state->addressIntentId);
        self::assertSame(PropertyAuthoringSourceCompleteness::IncompleteForPromotion, $state->completeness());
    }

    public function test_invalid_or_unavailable_geography_fails_closed(): void
    {
        $invalid = $this->enricher(GeographySelectionReplayStatus::Invalid)->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, null, $this->facts());
        $unavailable = $this->enricher(GeographySelectionReplayStatus::DependencyUnavailable)->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, null, $this->facts());

        self::assertSame(PropertyAuthoringEnrichmentStatus::Invalid, $invalid->status);
        self::assertNull($invalid->state);
        self::assertSame(PropertyAuthoringEnrichmentStatus::DependencyUnavailable, $unavailable->status);
        self::assertNull($unavailable->state);
    }

    public function test_address_intent_is_preserved_or_rotated_only_with_physical_address(): void
    {
        $first = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, null, $this->facts())->state;
        self::assertNotNull($first);

        $otherFact = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 2, $this->id(6), $first, ['rooms' => 5])->state;
        $sameAddress = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 2, $this->id(7), $first, ['addressLine' => ' 12   avenue Cheikh Anta Diop '])->state;
        $newLine = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 2, $this->id(8), $first, ['addressLine' => '14 avenue Cheikh Anta Diop'])->state;
        $newPlace = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 2, $this->id(9), $first, $this->proof($this->id(10)))->state;

        self::assertSame($first->addressIntentId, $otherFact?->addressIntentId);
        self::assertSame($first->addressIntentId, $sameAddress?->addressIntentId);
        self::assertNotSame($first->addressIntentId, $newLine?->addressIntentId);
        self::assertNotSame($first->addressIntentId, $newPlace?->addressIntentId);
    }

    public function test_checksum_is_stable_for_replay_and_diverges_for_changed_fact(): void
    {
        $first = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, null, $this->facts())->state;
        self::assertNotNull($first);
        $replay = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, $first, $this->facts())->state;
        $changed = $this->enricher()->enrich(self::PROPERTY, self::OWNER, 1, self::INTENT, $first, array_replace($this->facts(), ['rooms' => 5]))->state;

        self::assertSame($first->intentChecksum, $replay?->intentChecksum);
        self::assertNotSame($first->intentChecksum, $changed?->intentChecksum);
    }

    private function enricher(GeographySelectionReplayStatus $status = GeographySelectionReplayStatus::Validated): DeterministicPropertyAuthoringStateEnricherV1
    {
        return new DeterministicPropertyAuthoringStateEnricherV1(new class($status) implements GeographySelectionReplayValidatorV1
        {
            public function __construct(private readonly GeographySelectionReplayStatus $status) {}

            public function validate(string $geographicPlaceId, string $type, ?string $parentPlaceId, ?string $cursor, int $limit): GeographySelectionReplayResult
            {
                return new GeographySelectionReplayResult($this->status);
            }
        });
    }

    /** @return array<string, mixed> */
    private function facts(): array
    {
        return [
            'propertyType' => 'apartment',
            'city' => 'Dakar',
            'neighborhood' => 'Almadies',
            'propertyReference' => ' ref-001 ',
            'surfaceSquareMeters' => 120,
            'rooms' => 4,
            'bathrooms' => 2,
            'constructionYear' => 2020,
            'addressLine' => ' 12   avenue Cheikh Anta Diop ',
        ] + $this->proof(self::PLACE);
    }

    /** @return array<string, mixed> */
    private function proof(string $place): array
    {
        return [
            'geographicPlaceId' => $place,
            'geographicPlaceType' => 'neighborhood',
            'geographicParentPlaceId' => self::PARENT,
            'geographicSelectionCursor' => null,
            'geographicSelectionLimit' => 50,
        ];
    }

    private function id(int $suffix): string
    {
        return sprintf('a1000000-0000-4000-8000-%012d', $suffix);
    }
}
