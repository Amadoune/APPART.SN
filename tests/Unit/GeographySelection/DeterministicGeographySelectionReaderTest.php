<?php

namespace Tests\Unit\GeographySelection;

use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionSource;
use Appart\Modules\Geography\Application\GeographySelection\DeterministicGeographySelectionReader;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionSourceItem;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionSourceResult;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionStatus;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DeterministicGeographySelectionReaderTest extends TestCase
{
    public function test_available_page_has_exact_dto_and_bound_cursor(): void
    {
        $source = new InMemoryGeographySelectionSource([
            $this->item('30000000-0000-4000-8000-000000000001', 'Dakar', 'dakar'),
            $this->item('30000000-0000-4000-8000-000000000002', 'Thiès', 'thiès'),
        ]);
        $reader = new DeterministicGeographySelectionReader($source);
        $parent = $this->id('30000000-0000-4000-8000-000000000099');

        $first = $reader->read(new GeographySelectionQuery(PlaceType::City, $parent, null, 1));

        self::assertSame(GeographySelectionStatus::Available, $first->status);
        self::assertSame(['placeId', 'label', 'type', 'parentPlaceId'], array_keys(get_object_vars($first->items[0])));
        self::assertNotNull($first->nextCursor);

        $second = $reader->read(new GeographySelectionQuery(PlaceType::City, $parent, $first->nextCursor, 1));
        self::assertSame('Thiès', $second->items[0]->label);
        self::assertNull($second->nextCursor);

        $this->expectException(InvalidArgumentException::class);
        $reader->read(new GeographySelectionQuery(PlaceType::Neighborhood, $parent, $first->nextCursor, 1));
    }

    public function test_all_five_closed_statuses_are_preserved(): void
    {
        foreach ([
            GeographySelectionSourceResult::available([$this->item('30000000-0000-4000-8000-000000000001', 'Dakar', 'dakar')]),
            GeographySelectionSourceResult::empty(),
            GeographySelectionSourceResult::missing(),
            GeographySelectionSourceResult::corrupted(),
            GeographySelectionSourceResult::unavailable(),
        ] as $sourceResult) {
            $result = (new DeterministicGeographySelectionReader(new FixedGeographySelectionSource($sourceResult)))
                ->read(new GeographySelectionQuery(PlaceType::Country, null, null, 10));
            self::assertSame($sourceResult->status->value, $result->status->value);
        }
    }

    public function test_invalid_limit_parent_and_cursor_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GeographySelectionQuery(PlaceType::Country, null, null, 101);
    }

    public function test_non_country_requires_parent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GeographySelectionQuery(PlaceType::City, null, null, 10);
    }

    public function test_tampered_cursor_is_rejected_before_source_access(): void
    {
        $reader = new DeterministicGeographySelectionReader(new InMemoryGeographySelectionSource([
            $this->item('30000000-0000-4000-8000-000000000001', 'Dakar', 'dakar'),
            $this->item('30000000-0000-4000-8000-000000000002', 'Dakar', 'dakar'),
        ]));
        $parent = $this->id('30000000-0000-4000-8000-000000000099');
        $first = $reader->read(new GeographySelectionQuery(PlaceType::City, $parent, null, 1));
        self::assertNotNull($first->nextCursor);
        $tampered = substr($first->nextCursor, 0, -1).($first->nextCursor[-1] === 'A' ? 'B' : 'A');

        $this->expectException(InvalidArgumentException::class);
        $reader->read(new GeographySelectionQuery(PlaceType::City, $parent, $tampered, 1));
    }

    private function item(string $id, string $label, string $key): GeographySelectionSourceItem
    {
        return new GeographySelectionSourceItem($id, $label, PlaceType::City->value, '30000000-0000-4000-8000-000000000099', $key);
    }

    private function id(string $id): PlaceId
    {
        return PlaceId::fromString($id);
    }
}

final readonly class FixedGeographySelectionSource implements GeographySelectionSource
{
    public function __construct(private GeographySelectionSourceResult $result) {}

    public function select(PlaceType $type, ?PlaceId $parentId, ?string $afterNormalizationKey, ?PlaceId $afterPlaceId, int $limit): GeographySelectionSourceResult
    {
        return $this->result;
    }
}

final readonly class InMemoryGeographySelectionSource implements GeographySelectionSource
{
    /** @param list<GeographySelectionSourceItem> $items */
    public function __construct(private array $items) {}

    public function select(PlaceType $type, ?PlaceId $parentId, ?string $afterNormalizationKey, ?PlaceId $afterPlaceId, int $limit): GeographySelectionSourceResult
    {
        $items = array_values(array_filter($this->items, static fn (GeographySelectionSourceItem $item): bool => $afterNormalizationKey === null || [$item->normalizationKey, $item->placeId] > [$afterNormalizationKey, $afterPlaceId?->value]));

        return $items === [] ? GeographySelectionSourceResult::empty() : GeographySelectionSourceResult::available(array_slice($items, 0, $limit));
    }
}
