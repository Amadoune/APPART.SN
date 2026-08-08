<?php

namespace Tests\Unit\ContactsLeads\AntiAbuseOwnerReader;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerReader\OwnerLeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadAvailability;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadDiagnostics;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseStatusV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OwnerLeadIngressAntiAbuseReaderTest extends TestCase
{
    #[DataProvider('mappingCases')]
    public function test_mapping_is_mechanical(
        LeadIngressAntiAbuseRuntimeReadResult $runtimeResult,
        LeadIngressAntiAbuseStatusV1 $expected,
    ): void {
        $reader = new OwnerLeadIngressAntiAbuseReaderV1(new OwnerReaderRuntimeReadStub($runtimeResult));

        self::assertSame(
            $expected,
            $reader->read(
                LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005413'),
                new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('2026-07-31T09:00:00Z')),
            )->status,
        );
    }

    /** @return iterable<string, array{LeadIngressAntiAbuseRuntimeReadResult, LeadIngressAntiAbuseStatusV1}> */
    public static function mappingCases(): iterable
    {
        yield 'allowed' => [LeadIngressAntiAbuseRuntimeReadResult::allowed(), LeadIngressAntiAbuseStatusV1::Allowed];
        yield 'blocked' => [LeadIngressAntiAbuseRuntimeReadResult::blocked(), LeadIngressAntiAbuseStatusV1::Blocked];
        yield 'missing' => [LeadIngressAntiAbuseRuntimeReadResult::missing(), LeadIngressAntiAbuseStatusV1::Missing];
        yield 'corrupted' => [LeadIngressAntiAbuseRuntimeReadResult::corrupted(), LeadIngressAntiAbuseStatusV1::Corrupted];
        yield 'unavailable' => [LeadIngressAntiAbuseRuntimeReadResult::dependencyUnavailable(), LeadIngressAntiAbuseStatusV1::DependencyUnavailable];
    }
}

final readonly class OwnerReaderRuntimeReadStub implements LeadIngressAntiAbuseRuntimeReadV1
{
    public function __construct(private LeadIngressAntiAbuseRuntimeReadResult $result) {}

    public function read(LeadIngressIntentId $intentId, LeadIngressAntiAbuseObservedAt $observedAt): LeadIngressAntiAbuseRuntimeReadResult
    {
        return $this->result;
    }

    public function diagnostics(): LeadIngressAntiAbuseRuntimeReadDiagnostics
    {
        return new LeadIngressAntiAbuseRuntimeReadDiagnostics('stub', 'v1', LeadIngressAntiAbuseRuntimeReadAvailability::Available);
    }
}
