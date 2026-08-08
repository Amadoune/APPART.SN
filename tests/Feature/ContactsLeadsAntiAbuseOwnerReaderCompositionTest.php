<?php

namespace Tests\Feature;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerReader\OwnerLeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadAvailability;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadDiagnostics;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Contract\LeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Tests\TestCase;

final class ContactsLeadsAntiAbuseOwnerReaderCompositionTest extends TestCase
{
    public function test_reader_binding_is_lazy_unique_and_singleton(): void
    {
        $this->app->instance(LeadIngressAntiAbuseRuntimeReadV1::class, new OwnerReaderCompositionRuntimeReadStub);

        self::assertTrue($this->app->bound(LeadIngressAntiAbuseReaderV1::class));
        self::assertFalse($this->app->resolved(LeadIngressAntiAbuseReaderV1::class));

        $reader = $this->app->make(LeadIngressAntiAbuseReaderV1::class);
        self::assertInstanceOf(OwnerLeadIngressAntiAbuseReaderV1::class, $reader);
        self::assertSame($reader, $this->app->make(LeadIngressAntiAbuseReaderV1::class));
    }
}

final readonly class OwnerReaderCompositionRuntimeReadStub implements LeadIngressAntiAbuseRuntimeReadV1
{
    public function read(LeadIngressIntentId $intentId, LeadIngressAntiAbuseObservedAt $observedAt): LeadIngressAntiAbuseRuntimeReadResult
    {
        return LeadIngressAntiAbuseRuntimeReadResult::missing();
    }

    public function diagnostics(): LeadIngressAntiAbuseRuntimeReadDiagnostics
    {
        return new LeadIngressAntiAbuseRuntimeReadDiagnostics('stub', 'v1', LeadIngressAntiAbuseRuntimeReadAvailability::Available);
    }
}
