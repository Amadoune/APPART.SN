<?php

namespace Tests\Unit\ContactsLeads\ConsentPublicRead;

use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Contract\LeadContactConsentReaderV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentResultV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentStatusV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class LeadContactConsentContractsTest extends TestCase
{
    public function test_observed_at_is_immutable_utc_and_canonical(): void
    {
        $observedAt = new LeadConsentObservedAt(new DateTimeImmutable('2026-07-31T15:16:17.123456+02:00'));

        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('2026-07-31T13:16:17.123456Z', $observedAt->canonical());
        self::assertTrue((new ReflectionClass($observedAt))->isReadOnly());
        self::assertFalse((new ReflectionClass($observedAt))->hasMethod('now'));
    }

    public function test_status_catalogue_is_exact_and_result_has_no_payload(): void
    {
        self::assertSame(
            ['Granted', 'Denied', 'Missing', 'Corrupted', 'DependencyUnavailable'],
            array_map(static fn (LeadContactConsentStatusV1 $status): string => $status->name, LeadContactConsentStatusV1::cases()),
        );

        $result = new LeadContactConsentResultV1(LeadContactConsentStatusV1::Granted);
        self::assertSame(LeadContactConsentStatusV1::Granted, $result->status);
        self::assertSame(['status'], array_keys(get_object_vars($result)));
        self::assertTrue((new ReflectionClass($result))->isReadOnly());
    }

    public function test_reader_signature_is_exact_and_reuses_intent_identity(): void
    {
        $method = new ReflectionMethod(LeadContactConsentReaderV1::class, 'read');
        $parameters = $method->getParameters();

        self::assertCount(2, $parameters);
        self::assertSame(LeadIngressIntentId::class, (string) $parameters[0]->getType());
        self::assertSame(LeadConsentObservedAt::class, (string) $parameters[1]->getType());
        self::assertSame(LeadContactConsentResultV1::class, (string) $method->getReturnType());
    }
}
