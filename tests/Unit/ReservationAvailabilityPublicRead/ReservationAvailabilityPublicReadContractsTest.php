<?php

namespace Tests\Unit\ReservationAvailabilityPublicRead;

use Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\Contract\ListingReservationAvailabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\ListingReservationAvailabilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\ListingReservationAvailabilityResultV1;
use Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\ListingReservationAvailabilityStatusV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\Contract\PropertyReservationEligibilityReaderV1;
use Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\PropertyReservationEligibilityObservedAt;
use Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\PropertyReservationEligibilityResultV1;
use Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\PropertyReservationEligibilityStatusV1;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\Contract\ReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityResultV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityStatusV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ReservationAvailabilityPublicReadContractsTest extends TestCase
{
    #[Test]
    public function catalogues_are_closed_and_exact(): void
    {
        self::assertSame(
            ['reservable', 'not_reservable', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(ListingReservationAvailabilityStatusV1::cases(), 'value'),
        );
        self::assertSame(
            ['eligible', 'not_eligible', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(PropertyReservationEligibilityStatusV1::cases(), 'value'),
        );
        self::assertSame(
            ['available', 'conflicting', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(ReservationAvailabilityStatusV1::cases(), 'value'),
        );
    }

    #[Test]
    public function result_factories_cover_each_catalogue_without_payload(): void
    {
        self::assertSame(ListingReservationAvailabilityStatusV1::Reservable, ListingReservationAvailabilityResultV1::reservable()->status);
        self::assertSame(ListingReservationAvailabilityStatusV1::NotReservable, ListingReservationAvailabilityResultV1::notReservable()->status);
        self::assertSame(ListingReservationAvailabilityStatusV1::Missing, ListingReservationAvailabilityResultV1::missing()->status);
        self::assertSame(ListingReservationAvailabilityStatusV1::Corrupted, ListingReservationAvailabilityResultV1::corrupted()->status);
        self::assertSame(ListingReservationAvailabilityStatusV1::DependencyUnavailable, ListingReservationAvailabilityResultV1::dependencyUnavailable()->status);

        self::assertSame(PropertyReservationEligibilityStatusV1::Eligible, PropertyReservationEligibilityResultV1::eligible()->status);
        self::assertSame(PropertyReservationEligibilityStatusV1::NotEligible, PropertyReservationEligibilityResultV1::notEligible()->status);
        self::assertSame(PropertyReservationEligibilityStatusV1::Missing, PropertyReservationEligibilityResultV1::missing()->status);
        self::assertSame(PropertyReservationEligibilityStatusV1::Corrupted, PropertyReservationEligibilityResultV1::corrupted()->status);
        self::assertSame(PropertyReservationEligibilityStatusV1::DependencyUnavailable, PropertyReservationEligibilityResultV1::dependencyUnavailable()->status);

        self::assertSame(ReservationAvailabilityStatusV1::Available, ReservationAvailabilityResultV1::available()->status);
        self::assertSame(ReservationAvailabilityStatusV1::Conflicting, ReservationAvailabilityResultV1::conflicting()->status);
        self::assertSame(ReservationAvailabilityStatusV1::Missing, ReservationAvailabilityResultV1::missing()->status);
        self::assertSame(ReservationAvailabilityStatusV1::Corrupted, ReservationAvailabilityResultV1::corrupted()->status);
        self::assertSame(ReservationAvailabilityStatusV1::DependencyUnavailable, ReservationAvailabilityResultV1::dependencyUnavailable()->status);
    }

    #[Test]
    public function observation_instants_are_immutable_utc_and_canonical(): void
    {
        $instant = new DateTimeImmutable('2026-08-01 14:15:16.123456+02:00');

        foreach ([
            new ListingReservationAvailabilityObservedAt($instant),
            new PropertyReservationEligibilityObservedAt($instant),
            new ReservationAvailabilityObservedAt($instant),
        ] as $observedAt) {
            self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
            self::assertSame('2026-08-01T12:15:16.123456Z', $observedAt->canonical());
        }
    }

    #[Test]
    public function reader_signatures_are_owner_local_and_typed(): void
    {
        $this->assertSignature(ListingReservationAvailabilityReaderV1::class, ListingId::class, ListingReservationAvailabilityObservedAt::class, ListingReservationAvailabilityResultV1::class);
        $this->assertSignature(PropertyReservationEligibilityReaderV1::class, PropertyId::class, PropertyReservationEligibilityObservedAt::class, PropertyReservationEligibilityResultV1::class);
        $method = new ReflectionMethod(ReservationAvailabilityReaderV1::class, 'read');
        self::assertSame([
            ReservationAvailabilityIntentId::class,
            ReservationAvailabilitySubjectId::class,
            ReservationAvailabilityWindow::class,
            ReservationAvailabilityObservedAt::class,
        ], array_map(static fn ($parameter): string => (string) $parameter->getType(), $method->getParameters()));
        self::assertSame(ReservationAvailabilityResultV1::class, (string) $method->getReturnType());
    }

    #[Test]
    public function reservation_availability_context_is_owner_local_canonical_and_pre_intake(): void
    {
        $intentId = ReservationAvailabilityIntentId::fromString('01922f8e-7c44-7abc-8def-0123456789ab');
        $subjectId = ReservationAvailabilitySubjectId::fromString('01922f8e-7c44-7abc-8def-1123456789ab');
        $window = new ReservationAvailabilityWindow(
            new DateTimeImmutable('2026-08-10 14:00:00.000000+02:00'),
            new DateTimeImmutable('2026-08-12 10:00:00.000000+02:00'),
        );

        self::assertSame('01922f8e-7c44-7abc-8def-0123456789ab', $intentId->value);
        self::assertSame('01922f8e-7c44-7abc-8def-1123456789ab', $subjectId->value);
        self::assertSame('2026-08-10T12:00:00.000000Z/2026-08-12T08:00:00.000000Z', $window->canonical());
    }

    #[Test]
    public function reservation_availability_window_rejects_an_empty_window(): void
    {
        $instant = new DateTimeImmutable('2026-08-10T12:00:00Z');
        $this->expectException(InvalidArgumentException::class);

        new ReservationAvailabilityWindow($instant, $instant);
    }

    #[Test]
    public function reservation_availability_window_rejects_an_inverted_window(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ReservationAvailabilityWindow(
            new DateTimeImmutable('2026-08-12T08:00:00Z'),
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
        );
    }

    #[Test]
    public function owner_local_ids_reject_non_uuid_values(): void
    {
        foreach ([ReservationAvailabilityIntentId::class, ReservationAvailabilitySubjectId::class] as $type) {
            try {
                $type::fromString('external-listing-or-property-id');
                self::fail("{$type} accepted a non UUID value.");
            } catch (InvalidArgumentException) {
                self::addToAssertionCount(1);
            }
        }
    }

    /** @param class-string $contract */
    private function assertSignature(string $contract, string $identity, string $observedAt, string $result): void
    {
        $method = new ReflectionMethod($contract, 'read');
        self::assertTrue($method->getDeclaringClass()->isInterface());
        self::assertSame([$identity, $observedAt], array_map(
            static fn ($parameter): string => (string) $parameter->getType(),
            $method->getParameters(),
        ));
        self::assertSame($result, (string) $method->getReturnType());
    }
}
