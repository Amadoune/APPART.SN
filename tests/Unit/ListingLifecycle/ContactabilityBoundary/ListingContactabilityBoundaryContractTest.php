<?php

namespace Tests\Unit\ListingLifecycle\ContactabilityBoundary;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ContactabilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\Contract\ListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ListingContactabilityDecisionV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class ListingContactabilityBoundaryContractTest extends TestCase
{
    #[Test]
    public function decision_catalog_is_closed_and_fail_closed(): void
    {
        self::assertSame([
            'contactable',
            'not_contactable',
            'missing',
            'corrupted',
            'dependency_unavailable',
        ], array_column(ListingContactabilityDecisionV1::cases(), 'value'));

        self::assertSame(
            [ListingContactabilityDecisionV1::Contactable],
            array_filter(
                ListingContactabilityDecisionV1::cases(),
                static fn (ListingContactabilityDecisionV1 $decision): bool => $decision === ListingContactabilityDecisionV1::Contactable,
            ),
        );
        self::assertCount(
            count(ListingContactabilityDecisionV1::cases()),
            array_unique(array_column(ListingContactabilityDecisionV1::cases(), 'value')),
        );
    }

    #[Test]
    public function reader_signature_is_minimal_read_only_and_versioned(): void
    {
        $method = new ReflectionMethod(ListingContactabilityReaderV1::class, 'read');
        $parameters = $method->getParameters();

        self::assertSame('read', $method->getName());
        self::assertSame(2, $method->getNumberOfRequiredParameters());
        self::assertSame(ListingId::class, (string) $parameters[0]->getType());
        self::assertSame('listingId', $parameters[0]->getName());
        self::assertSame(ContactabilityObservedAt::class, (string) $parameters[1]->getType());
        self::assertSame('observedAt', $parameters[1]->getName());
        self::assertSame(ListingContactabilityDecisionV1::class, (string) $method->getReturnType());
    }

    #[Test]
    public function observed_at_is_immutable_utc_and_canonical(): void
    {
        $source = new DateTimeImmutable('2026-07-31T10:15:30.123456+02:00');
        $observedAt = new ContactabilityObservedAt($source);

        self::assertTrue((new ReflectionClass($observedAt))->isReadOnly());
        self::assertSame('2026-07-31T08:15:30.123456Z', $observedAt->canonical());
        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('+02:00', $source->format('P'));
    }
}
