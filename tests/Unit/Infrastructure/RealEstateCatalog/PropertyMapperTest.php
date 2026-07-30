<?php

namespace Tests\Unit\Infrastructure\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\AddressSnapshot;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PersistentPropertyIntegrity;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertySnapshot;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\RealEstateCatalog\FakePropertyRegistryHarness;

final class PropertyMapperTest extends TestCase
{
    private PropertyMapper $mapper;

    private FakePropertyRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $this->mapper = new PropertyMapper;
        $this->fixtures = new FakePropertyRegistryHarness;
    }

    #[DataProvider('aggregateProvider')]
    public function test_round_trip_preserves_all_observable_properties(string $fixture): void
    {
        $expected = $this->fixtures->{$fixture}();
        $actual = $this->mapper->toAggregate($this->mapper->toSnapshot($expected));

        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->reference()->value, $actual->reference()->value);
        self::assertSame($expected->type(), $actual->type());
        self::assertEquals($expected->surface(), $actual->surface());
        self::assertEquals($expected->rooms(), $actual->rooms());
        self::assertEquals($expected->bathrooms(), $actual->bathrooms());
        self::assertEquals($expected->constructionYear(), $actual->constructionYear());
        self::assertEquals($expected->address(), $actual->address());
        self::assertSame($expected->status(), $actual->status());
        self::assertEquals($expected->lastChangedAt(), $actual->lastChangedAt());
        self::assertSame($expected->version(), $actual->version());
        self::assertSame([], $actual->releaseEvents());
    }

    #[DataProvider('typeProvider')]
    public function test_each_property_type_and_optional_shape_reconstructs(string $type, ?int $surface, int $rooms, int $bathrooms, ?int $year, bool $address): void
    {
        $snapshot = $address
            ? $this->baseSnapshot($type, $surface, $rooms, $bathrooms, $year, $this->address())
            : new PropertySnapshot('33000000-0000-4000-8000-000000000001', 'PROP-CONTRACT-001', $type, $surface, $rooms, $bathrooms, $year, null, 'active', '2026-07-17T11:00:00.123456+00:00', 0);
        $property = $this->mapper->toAggregate($snapshot);

        self::assertSame($type, $property->type()->value);
        self::assertSame($surface, $property->surface()?->squareMeters);
        self::assertSame($address, $property->address() !== null);
    }

    public function test_next_mutation_after_reconstruction_uses_next_version_and_keeps_events_clean_beforehand(): void
    {
        $property = $this->mapper->toAggregate($this->mapper->toSnapshot($this->fixtures->minimalProperty()));
        self::assertSame([], $property->releaseEvents());
        $this->fixtures->mutate($property);

        self::assertSame(1, $property->version());
        self::assertNotEmpty($property->releaseEvents());
    }

    #[DataProvider('invalidProvider')]
    public function test_invalid_snapshots_are_refused(string $case): void
    {
        $base = $this->baseSnapshot();
        $invalid = match ($case) {
            'id' => $this->copy($base, id: 'invalid'),
            'reference' => $this->copy($base, reference: '?'),
            'type' => $this->copy($base, type: 'unknown'),
            'status' => $this->copy($base, status: 'unknown'),
            'version' => $this->copy($base, version: -1),
            'surface' => $this->copy($base, surface: -1),
            'rooms' => $this->copy($base, rooms: -1),
            'bathrooms' => $this->copy($base, bathrooms: -1),
            'year' => $this->copy($base, constructionYear: 1500),
            'address' => $this->copy($base, address: new AddressSnapshot('invalid', 'place:dakar', '12 avenue du Senegal')),
            'combination' => new PropertySnapshot($base->id, $base->reference, $base->type, null, $base->rooms, $base->bathrooms, $base->constructionYear, $base->address, $base->status, $base->lastChangedAt, $base->version),
            'date' => $this->copy($base, lastChangedAt: 'invalid'),
        };

        $this->expectException(PersistentPropertyIntegrity::class);
        $this->mapper->toAggregate($invalid);
    }

    public static function aggregateProvider(): array
    {
        return [['minimalProperty'], ['propertyWithHistory'], ['archivedProperty']];
    }

    public static function typeProvider(): array
    {
        return [
            ['apartment', 120, 5, 2, 2018, true],
            ['house', 120, 5, 2, 2018, true],
            ['villa', 120, 5, 2, 2018, true],
            ['land', 500, 0, 0, null, true],
            ['office', 120, 4, 1, 2020, true],
            ['commercial', 120, 1, 3, null, true],
            ['other', null, 0, 0, null, false],
        ];
    }

    public static function invalidProvider(): array
    {
        return array_map(static fn (string $case): array => [$case], ['id', 'reference', 'type', 'status', 'version', 'surface', 'rooms', 'bathrooms', 'year', 'address', 'combination', 'date']);
    }

    private function baseSnapshot(string $type = 'apartment', ?int $surface = 120, int $rooms = 5, int $bathrooms = 2, ?int $year = 2018, ?AddressSnapshot $address = null): PropertySnapshot
    {
        return new PropertySnapshot('33000000-0000-4000-8000-000000000001', 'PROP-CONTRACT-001', $type, $surface, $rooms, $bathrooms, $year, $address ?? $this->address(), 'active', '2026-07-17T11:00:00.123456+00:00', 0);
    }

    private function address(): AddressSnapshot
    {
        return new AddressSnapshot('33000000-0000-4000-8000-000000000101', 'place:dakar', '12 avenue du Senegal');
    }

    private function copy(PropertySnapshot $snapshot, ?string $id = null, ?string $reference = null, ?string $type = null, ?int $surface = null, ?int $rooms = null, ?int $bathrooms = null, ?int $constructionYear = null, ?AddressSnapshot $address = null, ?string $status = null, ?string $lastChangedAt = null, ?int $version = null): PropertySnapshot
    {
        return new PropertySnapshot($id ?? $snapshot->id, $reference ?? $snapshot->reference, $type ?? $snapshot->type, func_num_args() >= 5 ? $surface : $snapshot->surface, $rooms ?? $snapshot->rooms, $bathrooms ?? $snapshot->bathrooms, $constructionYear ?? $snapshot->constructionYear, $address ?? $snapshot->address, $status ?? $snapshot->status, $lastChangedAt ?? $snapshot->lastChangedAt, $version ?? $snapshot->version);
    }
}
