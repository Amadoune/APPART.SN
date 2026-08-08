<?php

namespace Tests\Feature;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Contract\LeadContactConsentReaderV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentStatusV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use Tests\TestCase;

final class ContactsLeadsConsentOwnerReaderCompositionTest extends TestCase
{
    public function test_reader_binding_is_lazy_unique_and_singleton(): void
    {
        $runtime = new OwnerReaderCompositionRuntimeStub;
        $this->app->instance(ConsentOwnerSourceRuntimeReadV1::class, $runtime);

        self::assertTrue($this->app->bound(LeadContactConsentReaderV1::class));
        self::assertSame(0, $runtime->reads);

        $reader = $this->app->make(LeadContactConsentReaderV1::class);
        self::assertSame($reader, $this->app->make(LeadContactConsentReaderV1::class));
        self::assertSame(0, $runtime->reads);

        $result = $reader->read(
            LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005405'),
            new LeadConsentObservedAt(new DateTimeImmutable('2026-07-31T10:00:00Z')),
        );
        self::assertSame(LeadContactConsentStatusV1::Granted, $result->status);
        self::assertSame(1, $runtime->reads);
    }
}

final class OwnerReaderCompositionRuntimeStub implements ConsentOwnerSourceRuntimeReadV1
{
    public int $reads = 0;

    public function read(
        LeadIngressIntentId $intentId,
        LeadConsentObservedAt $observedAt,
    ): ConsentOwnerSourceRuntimeReadResult {
        $this->reads++;

        return ConsentOwnerSourceRuntimeReadResult::granted();
    }

    public function diagnostics(): ConsentOwnerSourceRuntimeReadDiagnostics
    {
        return new ConsentOwnerSourceRuntimeReadDiagnostics('stub-v1', 'stub');
    }
}
