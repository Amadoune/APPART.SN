<?php

namespace Tests\Unit\ContactsLeads\ConsentOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConsentOwnerSourceRuntimeReadTest extends TestCase
{
    #[DataProvider('readCases')]
    public function test_runtime_read_reduces_owner_states_to_the_closed_catalogue(
        ConsentRevisionReadResult $sourceResult,
        ConsentOwnerSourceRuntimeReadStatus $expected,
    ): void {
        $runtime = $this->runtime(new RuntimeReadSourceStub($sourceResult));

        self::assertSame($expected, $runtime->read(self::intent(), self::observedAt())->status);
    }

    public function test_runtime_read_is_fail_closed_when_the_source_throws(): void
    {
        $runtime = $this->runtime(new ThrowingRuntimeReadSourceStub);

        self::assertSame(
            ConsentOwnerSourceRuntimeReadStatus::DependencyUnavailable,
            $runtime->read(self::intent(), self::observedAt())->status,
        );
    }

    public function test_diagnostics_are_closed_and_contain_no_persistence_detail(): void
    {
        $runtime = $this->runtime(new RuntimeReadSourceStub(ConsentRevisionReadResult::missing(self::intent())));
        $diagnostics = $runtime->diagnostics();

        self::assertSame('consent-owner-source-runtime-read-v1', $diagnostics->runtimeVersion);
        self::assertSame('contacts-leads.consent-owner-source-runtime-read', $diagnostics->component);
    }

    /** @return iterable<string, array{ConsentRevisionReadResult, ConsentOwnerSourceRuntimeReadStatus}> */
    public static function readCases(): iterable
    {
        yield 'granted' => [self::found(ConsentRevisionDecision::Granted), ConsentOwnerSourceRuntimeReadStatus::Granted];
        yield 'denied' => [self::found(ConsentRevisionDecision::Denied), ConsentOwnerSourceRuntimeReadStatus::Denied];
        yield 'undecided fails closed' => [self::found(ConsentRevisionDecision::Undecided), ConsentOwnerSourceRuntimeReadStatus::Denied];
        yield 'withdrawn fails closed' => [self::found(ConsentRevisionDecision::Withdrawn), ConsentOwnerSourceRuntimeReadStatus::Denied];
        yield 'missing' => [ConsentRevisionReadResult::missing(self::intent()), ConsentOwnerSourceRuntimeReadStatus::Missing];
        yield 'corrupted' => [ConsentRevisionReadResult::corrupted(self::intent()), ConsentOwnerSourceRuntimeReadStatus::Corrupted];
        yield 'dependency unavailable' => [ConsentRevisionReadResult::dependencyUnavailable(self::intent()), ConsentOwnerSourceRuntimeReadStatus::DependencyUnavailable];
    }

    private static function found(ConsentRevisionDecision $decision): ConsentRevisionReadResult
    {
        $effectiveAt = new DateTimeImmutable('2026-07-31T08:00:00Z');

        return ConsentRevisionReadResult::found(new ConsentRevisionState(
            self::intent(),
            1,
            $decision,
            $effectiveAt,
            $effectiveAt->modify('+1 minute'),
            'contact-v1',
        ));
    }

    private static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005401');
    }

    private static function observedAt(): LeadConsentObservedAt
    {
        return new LeadConsentObservedAt(new DateTimeImmutable('2026-07-31T09:00:00Z'));
    }

    private function runtime(ConsentOwnerSource $source): DeterministicConsentOwnerSourceRuntimeReadV1
    {
        return new DeterministicConsentOwnerSourceRuntimeReadV1(
            $source,
            new DeterministicConsentOwnerSourceRuntimeReadPolicy,
        );
    }
}

final readonly class RuntimeReadSourceStub implements ConsentOwnerSource
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

final readonly class ThrowingRuntimeReadSourceStub implements ConsentOwnerSource
{
    public function append(ConsentRevisionState $revision): ConsentRevisionWriteResult
    {
        return ConsentRevisionWriteResult::DependencyUnavailable;
    }

    public function at(LeadIngressIntentId $intentId, DateTimeImmutable $observedAt): ConsentRevisionReadResult
    {
        throw new RuntimeException('Unavailable source.');
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        return [];
    }
}
