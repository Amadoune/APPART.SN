<?php

namespace Tests\Unit\ContactsLeads\AntiAbuseOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadAvailability;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AntiAbuseOwnerSourceRuntimeReadTest extends TestCase
{
    #[DataProvider('readCases')]
    public function test_runtime_read_reduces_to_closed_catalogue(
        AntiAbuseRevisionReadResult $sourceResult,
        LeadIngressAntiAbuseRuntimeReadStatus $expected,
    ): void {
        self::assertSame($expected, $this->runtime(new RuntimeReadSourceStub($sourceResult))->read(self::intent(), self::observedAt())->status);
    }

    public function test_exception_is_fail_closed(): void
    {
        $runtime = $this->runtime(new ThrowingRuntimeReadSourceStub);

        self::assertSame(
            LeadIngressAntiAbuseRuntimeReadStatus::DependencyUnavailable,
            $runtime->read(self::intent(), self::observedAt())->status,
        );
        self::assertSame(LeadIngressAntiAbuseRuntimeReadAvailability::DependencyUnavailable, $runtime->diagnostics()->availability);
    }

    public function test_diagnostics_are_closed(): void
    {
        $diagnostics = $this->runtime(new RuntimeReadSourceStub(AntiAbuseRevisionReadResult::missing(self::intent())))->diagnostics();

        self::assertSame('contacts-leads.anti-abuse-owner-source-runtime-read', $diagnostics->runtimeReadId);
        self::assertSame('anti-abuse-owner-source-runtime-read-v1', $diagnostics->version);
        self::assertSame(LeadIngressAntiAbuseRuntimeReadAvailability::Available, $diagnostics->availability);
    }

    /** @return iterable<string, array{AntiAbuseRevisionReadResult, LeadIngressAntiAbuseRuntimeReadStatus}> */
    public static function readCases(): iterable
    {
        yield 'allowed' => [self::found(AntiAbuseRevisionDecision::Allowed), LeadIngressAntiAbuseRuntimeReadStatus::Allowed];
        yield 'blocked' => [self::found(AntiAbuseRevisionDecision::Blocked), LeadIngressAntiAbuseRuntimeReadStatus::Blocked];
        yield 'missing' => [AntiAbuseRevisionReadResult::missing(self::intent()), LeadIngressAntiAbuseRuntimeReadStatus::Missing];
        yield 'corrupted' => [AntiAbuseRevisionReadResult::corrupted(self::intent()), LeadIngressAntiAbuseRuntimeReadStatus::Corrupted];
        yield 'unavailable' => [AntiAbuseRevisionReadResult::dependencyUnavailable(self::intent()), LeadIngressAntiAbuseRuntimeReadStatus::DependencyUnavailable];
    }

    private static function found(AntiAbuseRevisionDecision $decision): AntiAbuseRevisionReadResult
    {
        $at = new DateTimeImmutable('2026-07-31T08:00:00Z');

        return AntiAbuseRevisionReadResult::found(new AntiAbuseRevisionState(self::intent(), 1, $decision, $at, $at, 'anti-abuse-v1'));
    }

    private static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005409');
    }

    private static function observedAt(): LeadIngressAntiAbuseObservedAt
    {
        return new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('2026-07-31T09:00:00Z'));
    }

    private function runtime(AntiAbuseOwnerSource $source): DeterministicLeadIngressAntiAbuseRuntimeReadV1
    {
        return new DeterministicLeadIngressAntiAbuseRuntimeReadV1($source, new DeterministicLeadIngressAntiAbuseRuntimeReadPolicy);
    }
}

final readonly class RuntimeReadSourceStub implements AntiAbuseOwnerSource
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

final readonly class ThrowingRuntimeReadSourceStub implements AntiAbuseOwnerSource
{
    public function append(AntiAbuseRevisionState $revision): AntiAbuseRevisionWriteResult
    {
        return AntiAbuseRevisionWriteResult::DependencyUnavailable;
    }

    public function at(LeadIngressIntentId $intentId, LeadIngressAntiAbuseObservedAt $observedAt): AntiAbuseRevisionReadResult
    {
        throw new RuntimeException('secret');
    }

    public function history(LeadIngressIntentId $intentId): array
    {
        return [];
    }
}
