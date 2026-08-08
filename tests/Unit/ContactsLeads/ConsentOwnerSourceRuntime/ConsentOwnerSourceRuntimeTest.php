<?php

namespace Tests\Unit\ContactsLeads\ConsentOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\ConsentOwnerSourceRuntimeAvailability;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\DeterministicConsentOwnerSourceRuntimeV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConsentOwnerSourceRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_runtime_is_deterministic_and_fail_closed(
        ConsentRevisionReadResult $readResult,
        ConsentOwnerSourceRuntimeAvailability $expected,
    ): void {
        $runtime = new DeterministicConsentOwnerSourceRuntimeV1(
            new DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy(new RuntimeSourceStub($readResult)),
        );

        self::assertSame($expected, $runtime->availability());
        self::assertSame($expected, $runtime->diagnostics()->availability);
        self::assertSame('consent-owner-source-runtime-v1', $runtime->diagnostics()->runtimeVersion);
        self::assertSame('contacts-leads.consent-owner-source', $runtime->diagnostics()->component);
    }

    /** @return iterable<string, array{ConsentRevisionReadResult, ConsentOwnerSourceRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        $id = LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005400');

        yield 'available' => [ConsentRevisionReadResult::missing($id), ConsentOwnerSourceRuntimeAvailability::Available];
        yield 'corrupted' => [ConsentRevisionReadResult::corrupted($id), ConsentOwnerSourceRuntimeAvailability::Corrupted];
        yield 'unavailable' => [ConsentRevisionReadResult::dependencyUnavailable($id), ConsentOwnerSourceRuntimeAvailability::DependencyUnavailable];
    }
}

final readonly class RuntimeSourceStub implements ConsentOwnerSource
{
    public function __construct(private ConsentRevisionReadResult $result) {}

    public function append(ConsentRevisionState $revision): ConsentRevisionWriteResult
    {
        return ConsentRevisionWriteResult::DependencyUnavailable;
    }

    public function at(LeadIngressIntentId $intentId, DateTimeImmutable $observedAt): ConsentRevisionReadResult
    {
        return $this->result;
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        return [];
    }
}
