<?php

namespace Tests\Unit\ContactsLeads\LeadIngressAntiAbusePublicRead;

use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Contract\LeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseResultV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseStatusV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class LeadIngressAntiAbuseContractsTest extends TestCase
{
    public function test_reader_signature_reuses_the_certified_intent_identity(): void
    {
        $method = new ReflectionMethod(LeadIngressAntiAbuseReaderV1::class, 'read');
        self::assertSame(LeadIngressIntentId::class, (string) $method->getParameters()[0]->getType());
        self::assertSame(LeadIngressAntiAbuseObservedAt::class, (string) $method->getParameters()[1]->getType());
        self::assertSame(LeadIngressAntiAbuseResultV1::class, (string) $method->getReturnType());
    }

    public function test_observed_at_is_immutable_utc_and_microsecond_canonical(): void
    {
        $observedAt = new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable(
            '2026-07-31T12:34:56.123456+02:00',
        ));

        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('2026-07-31T10:34:56.123456Z', $observedAt->canonical());
        self::assertTrue((new ReflectionClass($observedAt))->isReadOnly());
        self::assertNotSame(new DateTimeZone('Europe/Paris')->getName(), $observedAt->value->getTimezone()->getName());
    }

    public function test_catalogue_is_closed_and_only_allowed_permits_progression(): void
    {
        self::assertSame(
            ['allowed', 'blocked', 'missing', 'corrupted', 'dependency_unavailable'],
            array_map(static fn (LeadIngressAntiAbuseStatusV1 $status): string => $status->value, LeadIngressAntiAbuseStatusV1::cases()),
        );
        self::assertSame(
            [LeadIngressAntiAbuseStatusV1::Allowed],
            array_values(array_filter(
                LeadIngressAntiAbuseStatusV1::cases(),
                static fn (LeadIngressAntiAbuseStatusV1 $status): bool => $status === LeadIngressAntiAbuseStatusV1::Allowed,
            )),
        );
    }

    public function test_public_result_exposes_only_the_closed_status(): void
    {
        $reflection = new ReflectionClass(LeadIngressAntiAbuseResultV1::class);
        self::assertTrue($reflection->isReadOnly());
        self::assertSame(['status'], array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            $reflection->getProperties(),
        ));
        self::assertSame(LeadIngressAntiAbuseStatusV1::Blocked, (new LeadIngressAntiAbuseResultV1(LeadIngressAntiAbuseStatusV1::Blocked))->status);
    }
}
