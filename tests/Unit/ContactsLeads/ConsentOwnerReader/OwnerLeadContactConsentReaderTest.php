<?php

namespace Tests\Unit\ContactsLeads\ConsentOwnerReader;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerReader\OwnerLeadContactConsentReaderV1;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentStatusV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OwnerLeadContactConsentReaderTest extends TestCase
{
    #[DataProvider('mappingCases')]
    public function test_reader_maps_each_runtime_status_without_translation(
        ConsentOwnerSourceRuntimeReadStatus $runtimeStatus,
        LeadContactConsentStatusV1 $expected,
    ): void {
        $runtime = new OwnerReaderRuntimeStub(self::runtimeResult($runtimeStatus));
        $reader = new OwnerLeadContactConsentReaderV1($runtime);

        $result = $reader->read(self::intent(), self::observedAt());

        self::assertSame($expected, $result->status);
        self::assertSame(self::intent()->value, $runtime->intentId?->value);
        self::assertSame(self::observedAt()->canonical(), $runtime->observedAt?->canonical());
    }

    /** @return iterable<string, array{ConsentOwnerSourceRuntimeReadStatus, LeadContactConsentStatusV1}> */
    public static function mappingCases(): iterable
    {
        foreach (ConsentOwnerSourceRuntimeReadStatus::cases() as $status) {
            yield $status->value => [$status, LeadContactConsentStatusV1::from($status->value)];
        }
    }

    private static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005404');
    }

    private static function runtimeResult(ConsentOwnerSourceRuntimeReadStatus $status): ConsentOwnerSourceRuntimeReadResult
    {
        return match ($status) {
            ConsentOwnerSourceRuntimeReadStatus::Granted => ConsentOwnerSourceRuntimeReadResult::granted(),
            ConsentOwnerSourceRuntimeReadStatus::Denied => ConsentOwnerSourceRuntimeReadResult::denied(),
            ConsentOwnerSourceRuntimeReadStatus::Missing => ConsentOwnerSourceRuntimeReadResult::missing(),
            ConsentOwnerSourceRuntimeReadStatus::Corrupted => ConsentOwnerSourceRuntimeReadResult::corrupted(),
            ConsentOwnerSourceRuntimeReadStatus::DependencyUnavailable => ConsentOwnerSourceRuntimeReadResult::dependencyUnavailable(),
        };
    }

    private static function observedAt(): LeadConsentObservedAt
    {
        return new LeadConsentObservedAt(new DateTimeImmutable('2026-07-31T10:00:00Z'));
    }
}

final class OwnerReaderRuntimeStub implements ConsentOwnerSourceRuntimeReadV1
{
    public ?LeadIngressIntentId $intentId = null;

    public ?LeadConsentObservedAt $observedAt = null;

    public function __construct(private readonly ConsentOwnerSourceRuntimeReadResult $result) {}

    public function read(
        LeadIngressIntentId $intentId,
        LeadConsentObservedAt $observedAt,
    ): ConsentOwnerSourceRuntimeReadResult {
        $this->intentId = $intentId;
        $this->observedAt = $observedAt;

        return $this->result;
    }

    public function diagnostics(): ConsentOwnerSourceRuntimeReadDiagnostics
    {
        return new ConsentOwnerSourceRuntimeReadDiagnostics('stub-v1', 'stub');
    }
}
