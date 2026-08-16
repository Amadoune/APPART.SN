<?php

namespace Tests\Unit\PropertyAuthoringGeographySelection;

use App\Application\PropertyAuthoringGeographySelection\DeterministicGeographySelectionReplayValidatorV1;
use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayStatus;
use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionItem;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionResult;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GeographySelectionReplayValidatorV1Test extends TestCase
{
    private const string PLACE = '51000000-0000-4000-8000-000000000001';

    private const string PARENT = '51000000-0000-4000-8000-000000000002';

    public function test_exact_item_returned_by_replayed_page_is_validated(): void
    {
        $validator = new DeterministicGeographySelectionReplayValidatorV1(new ReplayReader(new GeographySelectionResult(
            GeographySelectionStatus::Available,
            [new GeographySelectionItem(self::PLACE, 'Dakar', 'city', self::PARENT)],
        )));

        self::assertSame(GeographySelectionReplayStatus::Validated, $validator->validate(self::PLACE, 'city', self::PARENT, null, 50)->status);
    }

    /** @return iterable<string, array{GeographySelectionResult}> */
    public static function invalidResults(): iterable
    {
        yield 'id absent' => [new GeographySelectionResult(GeographySelectionStatus::Available, [new GeographySelectionItem('51000000-0000-4000-8000-000000000009', 'Thiès', 'city', self::PARENT)])];
        yield 'empty' => [new GeographySelectionResult(GeographySelectionStatus::Empty)];
        yield 'missing' => [new GeographySelectionResult(GeographySelectionStatus::Missing)];
        yield 'corrupted' => [new GeographySelectionResult(GeographySelectionStatus::Corrupted)];
    }

    #[DataProvider('invalidResults')]
    public function test_absent_and_closed_non_available_results_are_invalid(GeographySelectionResult $result): void
    {
        $validator = new DeterministicGeographySelectionReplayValidatorV1(new ReplayReader($result));

        self::assertSame(GeographySelectionReplayStatus::Invalid, $validator->validate(self::PLACE, 'city', self::PARENT, null, 50)->status);
    }

    public function test_dependency_unavailable_remains_distinct(): void
    {
        $validator = new DeterministicGeographySelectionReplayValidatorV1(new ReplayReader(new GeographySelectionResult(GeographySelectionStatus::DependencyUnavailable)));

        self::assertSame(GeographySelectionReplayStatus::DependencyUnavailable, $validator->validate(self::PLACE, 'city', self::PARENT, null, 50)->status);
    }

    public function test_wrong_type_parent_uuid_and_divergent_cursor_are_invalid(): void
    {
        $reader = new ReplayReader(new GeographySelectionResult(GeographySelectionStatus::Available, [new GeographySelectionItem(self::PLACE, 'Dakar', 'city', self::PARENT)]));
        $validator = new DeterministicGeographySelectionReplayValidatorV1($reader);

        self::assertSame(GeographySelectionReplayStatus::Invalid, $validator->validate(self::PLACE, 'neighborhood', self::PARENT, null, 50)->status);
        self::assertSame(GeographySelectionReplayStatus::Invalid, $validator->validate(self::PLACE, 'city', '51000000-0000-4000-8000-000000000003', null, 50)->status);
        self::assertSame(GeographySelectionReplayStatus::Invalid, $validator->validate('not-a-uuid', 'city', self::PARENT, null, 50)->status);

        $reader->rejectCursor = true;
        self::assertSame(GeographySelectionReplayStatus::Invalid, $validator->validate(self::PLACE, 'city', self::PARENT, 'cross-scope', 50)->status);
    }
}

final class ReplayReader implements GeographySelectionReaderV1
{
    public bool $rejectCursor = false;

    public function __construct(private readonly GeographySelectionResult $result) {}

    public function read(GeographySelectionQuery $query): GeographySelectionResult
    {
        if ($this->rejectCursor) {
            throw new InvalidArgumentException('Divergent cursor.');
        }

        return $this->result;
    }
}
