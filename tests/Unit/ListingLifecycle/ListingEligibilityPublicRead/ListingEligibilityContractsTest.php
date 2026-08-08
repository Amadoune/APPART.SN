<?php

namespace Tests\Unit\ListingLifecycle\ListingEligibilityPublicRead;

use Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\Contract\ListingEligibilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\ListingEligibilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\ListingEligibilityResultV1;
use Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\ListingEligibilityStatusV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ListingEligibilityContractsTest extends TestCase
{
    #[Test]
    public function catalogue_is_closed_and_exact(): void
    {
        self::assertSame(
            ['eligible', 'not_eligible', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(ListingEligibilityStatusV1::cases(), 'value'),
        );
    }

    #[Test]
    public function result_contains_only_the_closed_status(): void
    {
        self::assertSame(ListingEligibilityStatusV1::Eligible, ListingEligibilityResultV1::eligible()->status);
        self::assertSame(ListingEligibilityStatusV1::NotEligible, ListingEligibilityResultV1::notEligible()->status);
        self::assertSame(ListingEligibilityStatusV1::Missing, ListingEligibilityResultV1::missing()->status);
        self::assertSame(ListingEligibilityStatusV1::Corrupted, ListingEligibilityResultV1::corrupted()->status);
        self::assertSame(ListingEligibilityStatusV1::DependencyUnavailable, ListingEligibilityResultV1::dependencyUnavailable()->status);
        self::assertSame(['status'], array_keys(get_object_vars(ListingEligibilityResultV1::eligible())));
    }

    #[Test]
    public function observation_is_immutable_utc_canonical_and_microsecond_precise(): void
    {
        $observedAt = new ListingEligibilityObservedAt(
            new DateTimeImmutable('2026-08-01 14:15:16.123456+02:00'),
        );

        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('2026-08-01T12:15:16.123456Z', $observedAt->canonical());
        self::assertFalse(method_exists($observedAt, 'now'));
    }

    #[Test]
    public function reader_signature_reuses_listing_identity_and_explicit_observation(): void
    {
        $method = new ReflectionMethod(ListingEligibilityReaderV1::class, 'read');

        self::assertTrue($method->getDeclaringClass()->isInterface());
        self::assertSame(
            [ListingId::class, ListingEligibilityObservedAt::class],
            array_map(static fn ($parameter): string => (string) $parameter->getType(), $method->getParameters()),
        );
        self::assertSame(ListingEligibilityResultV1::class, (string) $method->getReturnType());
    }
}
