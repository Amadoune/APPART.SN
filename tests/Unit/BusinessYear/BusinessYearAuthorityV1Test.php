<?php

namespace Tests\Unit\BusinessYear;

use Appart\Modules\RealEstateCatalog\Application\BusinessYear\BusinessYearResolutionStatus;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\PropertyDecisionOccurredAt;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BusinessYearAuthorityV1Test extends TestCase
{
    /** @return iterable<string, array{string, int}> */
    public static function vectors(): iterable
    {
        yield 'normal UTC instant' => ['2026-06-14T12:30:00.123456Z', 2026];
        yield 'last microsecond of 2026 UTC' => ['2026-12-31T23:59:59.999999Z', 2026];
        yield 'first microsecond of 2027 UTC' => ['2027-01-01T00:00:00.000000Z', 2027];
        yield 'positive offset local 2027 but UTC 2026' => ['2027-01-01T00:30:00.000000+01:00', 2026];
        yield 'negative offset local 2026 but UTC 2027' => ['2026-12-31T23:30:00.000000-02:00', 2027];
    }

    #[DataProvider('vectors')]
    public function test_literal_utc_calendar_vectors(string $instant, int $expectedYear): void
    {
        $result = (new UtcCalendarBusinessYearAuthorityV1)->resolve(PropertyDecisionOccurredAt::fromString($instant));

        self::assertSame(BusinessYearResolutionStatus::Resolved, $result->status);
        self::assertSame($expectedYear, $result->businessYear->value);
        self::assertSame($expectedYear, BusinessYear::fromInt($result->businessYear->value)->value);
    }

    public function test_equivalent_offsets_converge_across_instances_and_replay(): void
    {
        $utc = PropertyDecisionOccurredAt::fromString('2026-12-31T23:30:00.000000Z');
        $offset = PropertyDecisionOccurredAt::fromString('2027-01-01T00:30:00.000000+01:00');

        $first = (new UtcCalendarBusinessYearAuthorityV1)->resolve($utc);
        $second = (new UtcCalendarBusinessYearAuthorityV1)->resolve($offset);
        $replay = (new UtcCalendarBusinessYearAuthorityV1)->resolve(PropertyDecisionOccurredAt::fromString('2026-12-31T23:30:00.000000Z'));

        self::assertSame(2026, $first->businessYear->value);
        self::assertSame($first->businessYear->value, $second->businessYear->value);
        self::assertSame($first->businessYear->value, $replay->businessYear->value);
    }

    public function test_instant_without_explicit_timezone_is_rejected_before_authority(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PropertyDecisionOccurredAt::fromString('2026-12-31T23:59:59.999999');
    }

    public function test_invalid_calendar_instant_is_rejected_before_authority(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PropertyDecisionOccurredAt::fromString('2026-02-30T10:00:00.000000Z');
    }
}
