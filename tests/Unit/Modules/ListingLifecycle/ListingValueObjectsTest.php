<?php

namespace Tests\Unit\Modules\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\SuspensionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ListingValueObjectsTest extends TestCase
{
    public function test_official_statuses_are_exact(): void
    {
        self::assertSame(['draft', 'submitted', 'under_review', 'changes_requested', 'published', 'suspended', 'expired', 'withdrawn', 'rejected', 'archived'], array_column(ListingStatus::cases(), 'value'));
    }

    public function test_reasons_are_normalized(): void
    {
        self::assertSame('Contrôle favorable', PublicationReason::fromString(' Contrôle   favorable ')->value);
        self::assertSame('Risque confirmé', SuspensionReason::fromString(' Risque   confirmé ')->value);
    }

    public function test_expiration_must_follow_publication(): void
    {
        $this->expectException(InvalidListingValue::class);
        ExpirationDate::fromDateTime(new DateTimeImmutable('2026-07-16'))->ensureAfter(new DateTimeImmutable('2026-07-16'));
    }

    public function test_transition_reason_is_mandatory(): void
    {
        $this->expectException(InvalidListingValue::class);
        TransitionReason::fromString('');
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $type, string $value): void
    {
        $this->expectException(InvalidListingValue::class);
        match ($type) {
            'listing' => ListingId::fromString($value),
            'property' => PropertyId::fromString($value),
            'revision' => ListingRevisionId::fromString($value),
            'publication_reason' => PublicationReason::fromString($value),
            'suspension_reason' => SuspensionReason::fromString($value),
        };
    }

    /** @return array<string, array{string, string}> */
    public static function invalidValues(): array
    {
        return ['listing' => ['listing', 'x'], 'property' => ['property', 'x'], 'revision' => ['revision', 'x'], 'publication_reason' => ['publication_reason', 'x'], 'suspension_reason' => ['suspension_reason', 'x']];
    }
}
