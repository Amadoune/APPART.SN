<?php

namespace Tests\Feature;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use Tests\TestCase;

final class ContactsLeadsConsentOwnerSourceRuntimeReadCompositionTest extends TestCase
{
    public function test_runtime_read_binding_is_lazy_unique_and_singleton(): void
    {
        $source = new RuntimeReadCompositionSourceStub;
        $this->app->instance(ConsentOwnerSource::class, $source);

        self::assertTrue($this->app->bound(ConsentOwnerSourceRuntimeReadV1::class));
        self::assertSame(0, $source->reads);

        $runtime = $this->app->make(ConsentOwnerSourceRuntimeReadV1::class);
        self::assertSame($runtime, $this->app->make(ConsentOwnerSourceRuntimeReadV1::class));
        self::assertSame(0, $source->reads);

        $result = $runtime->read(
            LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005402'),
            new LeadConsentObservedAt(new DateTimeImmutable('2026-07-31T09:00:00Z')),
        );
        self::assertSame(ConsentOwnerSourceRuntimeReadStatus::Missing, $result->status);
        self::assertSame(1, $source->reads);
    }
}

final class RuntimeReadCompositionSourceStub implements ConsentOwnerSource
{
    public int $reads = 0;

    public function append(ConsentRevisionState $revision): ConsentRevisionWriteResult
    {
        return ConsentRevisionWriteResult::DependencyUnavailable;
    }

    public function at(LeadIngressIntentId $intentId, DateTimeImmutable $observedAt): ConsentRevisionReadResult
    {
        $this->reads++;

        return ConsentRevisionReadResult::missing($intentId);
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        return [];
    }
}
