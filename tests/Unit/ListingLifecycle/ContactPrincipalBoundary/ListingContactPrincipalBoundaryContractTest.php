<?php

namespace Tests\Unit\ListingLifecycle\ContactPrincipalBoundary;

use Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\ContactPrincipalObservedAt;
use Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\Contract\ListingContactPrincipalReaderV1;
use Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\ListingContactPrincipalResultV1;
use Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\ListingContactPrincipalStatusV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class ListingContactPrincipalBoundaryContractTest extends TestCase
{
    #[Test]
    public function result_catalog_is_closed_and_only_resolved_exposes_account_id(): void
    {
        self::assertSame([
            'resolved',
            'missing',
            'not_assigned',
            'ambiguous',
            'corrupted',
            'dependency_unavailable',
        ], array_column(ListingContactPrincipalStatusV1::cases(), 'value'));

        $accountId = '019428b8-5d5d-7c28-8a8f-8796c8732f91';
        $resolved = ListingContactPrincipalResultV1::resolved($accountId);

        self::assertSame(ListingContactPrincipalStatusV1::Resolved, $resolved->status);
        self::assertSame($accountId, $resolved->accountId);

        foreach ([
            ListingContactPrincipalResultV1::missing(),
            ListingContactPrincipalResultV1::notAssigned(),
            ListingContactPrincipalResultV1::ambiguous(),
            ListingContactPrincipalResultV1::corrupted(),
            ListingContactPrincipalResultV1::dependencyUnavailable(),
        ] as $closedResult) {
            self::assertNull($closedResult->accountId);
        }
    }

    #[Test]
    public function result_invariant_rejects_an_account_id_for_a_non_resolved_status(): void
    {
        $reflection = new ReflectionClass(ListingContactPrincipalResultV1::class);
        $constructor = $reflection->getConstructor();
        self::assertNotNull($constructor);

        $this->expectException(LogicException::class);
        $constructor->invoke(
            $reflection->newInstanceWithoutConstructor(),
            ListingContactPrincipalStatusV1::Missing,
            '019428b8-5d5d-7c28-8a8f-8796c8732f91',
        );
    }

    #[Test]
    public function reader_signature_is_minimal_read_only_and_versioned(): void
    {
        $method = new ReflectionMethod(ListingContactPrincipalReaderV1::class, 'read');
        $parameters = $method->getParameters();

        self::assertSame(2, $method->getNumberOfRequiredParameters());
        self::assertSame(ListingId::class, (string) $parameters[0]->getType());
        self::assertSame('listingId', $parameters[0]->getName());
        self::assertSame(ContactPrincipalObservedAt::class, (string) $parameters[1]->getType());
        self::assertSame('observedAt', $parameters[1]->getName());
        self::assertSame(ListingContactPrincipalResultV1::class, (string) $method->getReturnType());
    }

    #[Test]
    public function observed_at_is_immutable_utc_and_canonical(): void
    {
        $source = new DateTimeImmutable('2026-07-31T10:15:30.123456+02:00');
        $observedAt = new ContactPrincipalObservedAt($source);

        self::assertTrue((new ReflectionClass($observedAt))->isReadOnly());
        self::assertSame('2026-07-31T08:15:30.123456Z', $observedAt->canonical());
        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
    }
}
