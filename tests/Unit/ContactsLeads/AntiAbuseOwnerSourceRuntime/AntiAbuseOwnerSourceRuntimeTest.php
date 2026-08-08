<?php

namespace Tests\Unit\ContactsLeads\AntiAbuseOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\AntiAbuseOwnerSourceRuntimeAvailability;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\DeterministicAntiAbuseOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AntiAbuseOwnerSourceRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_runtime_is_deterministic_and_fail_closed(
        AntiAbuseRevisionReadResult $readResult,
        AntiAbuseOwnerSourceRuntimeAvailability $expected,
    ): void {
        $runtime = $this->runtime(new RuntimeSourceStub($readResult));

        self::assertSame($expected, $runtime->availability());
        self::assertSame($expected, $runtime->diagnostics()->availability);
        self::assertSame('contacts-leads.anti-abuse-owner-source', $runtime->diagnostics()->runtimeId);
        self::assertSame('anti-abuse-owner-source-runtime-v1', $runtime->diagnostics()->version);
    }

    public function test_exception_is_dependency_unavailable_without_diagnostic_leak(): void
    {
        $runtime = $this->runtime(new ThrowingRuntimeSourceStub);

        self::assertSame(AntiAbuseOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame(
            ['contacts-leads.anti-abuse-owner-source', 'anti-abuse-owner-source-runtime-v1', 'dependency_unavailable'],
            [
                $runtime->diagnostics()->runtimeId,
                $runtime->diagnostics()->version,
                $runtime->diagnostics()->availability->value,
            ],
        );
    }

    /** @return iterable<string, array{AntiAbuseRevisionReadResult, AntiAbuseOwnerSourceRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        $id = LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005408');

        yield 'found' => [AntiAbuseRevisionReadResult::found(AntiAbuseRuntimeFixture::revision($id)), AntiAbuseOwnerSourceRuntimeAvailability::Available];
        yield 'missing' => [AntiAbuseRevisionReadResult::missing($id), AntiAbuseOwnerSourceRuntimeAvailability::Available];
        yield 'corrupted' => [AntiAbuseRevisionReadResult::corrupted($id), AntiAbuseOwnerSourceRuntimeAvailability::Corrupted];
        yield 'unavailable' => [AntiAbuseRevisionReadResult::dependencyUnavailable($id), AntiAbuseOwnerSourceRuntimeAvailability::DependencyUnavailable];
    }

    private function runtime(AntiAbuseOwnerSource $source): DeterministicAntiAbuseOwnerSourceRuntimeV1
    {
        return new DeterministicAntiAbuseOwnerSourceRuntimeV1(
            new DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy($source),
        );
    }
}

final readonly class RuntimeSourceStub implements AntiAbuseOwnerSource
{
    public function __construct(private AntiAbuseRevisionReadResult $result) {}

    public function append(AntiAbuseRevisionState $revision): AntiAbuseRevisionWriteResult
    {
        return AntiAbuseRevisionWriteResult::DependencyUnavailable;
    }

    public function at(LeadIngressIntentId $intentId, LeadIngressAntiAbuseObservedAt $observedAt): AntiAbuseRevisionReadResult
    {
        return $this->result;
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        return [];
    }
}

final readonly class ThrowingRuntimeSourceStub implements AntiAbuseOwnerSource
{
    public function append(AntiAbuseRevisionState $revision): AntiAbuseRevisionWriteResult
    {
        throw new RuntimeException('secret infrastructure diagnostic');
    }

    public function at(LeadIngressIntentId $intentId, LeadIngressAntiAbuseObservedAt $observedAt): AntiAbuseRevisionReadResult
    {
        throw new RuntimeException('secret infrastructure diagnostic');
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        throw new RuntimeException('secret infrastructure diagnostic');
    }
}

final class AntiAbuseRuntimeFixture
{
    public static function revision(LeadIngressIntentId $id): AntiAbuseRevisionState
    {
        $effectiveAt = new \DateTimeImmutable('2026-07-31T08:00:00Z');

        return new AntiAbuseRevisionState(
            $id,
            1,
            AntiAbuseRevisionDecision::Allowed,
            $effectiveAt,
            $effectiveAt,
            'anti-abuse-v1',
        );
    }
}
