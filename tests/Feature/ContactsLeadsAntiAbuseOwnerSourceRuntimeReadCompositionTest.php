<?php

namespace Tests\Feature;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use Tests\TestCase;

final class ContactsLeadsAntiAbuseOwnerSourceRuntimeReadCompositionTest extends TestCase
{
    public function test_binding_is_lazy_unique_singleton_and_composed_from_owner_port(): void
    {
        $source = new AntiAbuseRuntimeReadCompositionSourceStub;
        $this->app->instance(AntiAbuseOwnerSource::class, $source);

        self::assertTrue($this->app->bound(LeadIngressAntiAbuseRuntimeReadV1::class));
        self::assertFalse($this->app->resolved(LeadIngressAntiAbuseRuntimeReadV1::class));

        $runtime = $this->app->make(LeadIngressAntiAbuseRuntimeReadV1::class);
        self::assertSame($runtime, $this->app->make(LeadIngressAntiAbuseRuntimeReadV1::class));
        self::assertSame(0, $source->reads);
        self::assertSame(
            LeadIngressAntiAbuseRuntimeReadStatus::Missing,
            $runtime->read(
                LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005410'),
                new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('2026-07-31T09:00:00Z')),
            )->status,
        );
        self::assertSame(1, $source->reads);
    }
}

final class AntiAbuseRuntimeReadCompositionSourceStub implements AntiAbuseOwnerSource
{
    public int $reads = 0;

    public function append(AntiAbuseRevisionState $revision): AntiAbuseRevisionWriteResult
    {
        return AntiAbuseRevisionWriteResult::DependencyUnavailable;
    }

    public function at(LeadIngressIntentId $intentId, LeadIngressAntiAbuseObservedAt $observedAt): AntiAbuseRevisionReadResult
    {
        $this->reads++;

        return AntiAbuseRevisionReadResult::missing($intentId);
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        return [];
    }
}
