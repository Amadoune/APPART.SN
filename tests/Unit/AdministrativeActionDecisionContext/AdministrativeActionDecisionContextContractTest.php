<?php

namespace Tests\Unit\AdministrativeActionDecisionContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextChecksum;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextResolution;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextResolutionStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextTechnicalDiagnostic;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionRecordingDisposition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AdministrativeActionDecisionContextContractTest extends TestCase
{
    public function test_direct_recording_requires_the_author_as_decision_actor(): void
    {
        $author = $this->actor('author-001');
        $authority = AdministrativeActionDecisionAuthority::directRecording($author, $author);

        self::assertSame(AdministrativeActionRecordingDisposition::DirectRecording, $authority->disposition);
        self::assertSame($author, $authority->author);
        self::assertSame($author, $authority->decisionActor);
    }

    public function test_direct_recording_rejects_a_distinct_decision_actor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionDecisionAuthority::directRecording(
            $this->actor('author-001'),
            $this->actor('decider-001'),
        );
    }

    public function test_independent_approval_requires_a_distinct_decision_actor(): void
    {
        $author = $this->actor('author-001');
        $decider = $this->actor('decider-001');
        $authority = AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decider);

        self::assertSame(AdministrativeActionRecordingDisposition::IndependentApprovalRequired, $authority->disposition);
        self::assertSame($author, $authority->author);
        self::assertSame($decider, $authority->decisionActor);
    }

    public function test_independent_approval_rejects_self_decision(): void
    {
        $author = $this->actor('author-001');
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $author);
    }

    public function test_reason_evidence_is_closed_and_never_carries_reason_content(): void
    {
        self::assertSame(['present', 'missing'], array_column(AdministrativeActionReasonEvidence::cases(), 'value'));
        self::assertSame(['Present', 'Missing'], array_column(AdministrativeActionReasonEvidence::cases(), 'name'));
    }

    public function test_recording_dispositions_are_closed(): void
    {
        self::assertSame(
            ['direct_recording', 'independent_approval_required'],
            array_column(AdministrativeActionRecordingDisposition::cases(), 'value'),
        );
    }

    public function test_context_is_versioned_immutable_and_deterministic(): void
    {
        $first = $this->context();
        $second = $this->context();

        self::assertSame(AdministrativeActionDecisionContextVersion::V1, $first->contractVersion);
        self::assertEquals($first, $second);
        self::assertEquals($first->checksum(), $second->checksum());
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum()->value);
    }

    public function test_every_business_evidence_changes_the_checksum(): void
    {
        $present = $this->context();
        $missing = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Missing,
            $present->authority,
        );

        self::assertNotSame($present->checksum()->value, $missing->checksum()->value);
    }

    public function test_invalid_checksum_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionDecisionContextChecksum::fromString('invalid');
    }

    public function test_available_resolution_contains_only_business_context(): void
    {
        $context = $this->context();
        $resolution = AdministrativeActionDecisionContextResolution::available($context);

        self::assertSame(AdministrativeActionDecisionContextResolutionStatus::Available, $resolution->status);
        self::assertSame($context, $resolution->context);
        self::assertNull($resolution->technicalDiagnostic);
    }

    #[DataProvider('technicalDiagnostics')]
    public function test_unavailable_resolution_contains_only_a_technical_diagnostic(
        AdministrativeActionDecisionContextTechnicalDiagnostic $diagnostic,
    ): void {
        $resolution = AdministrativeActionDecisionContextResolution::unavailable($diagnostic);

        self::assertSame(AdministrativeActionDecisionContextResolutionStatus::Unavailable, $resolution->status);
        self::assertNull($resolution->context);
        self::assertSame($diagnostic, $resolution->technicalDiagnostic);
    }

    /** @return iterable<string, array{AdministrativeActionDecisionContextTechnicalDiagnostic}> */
    public static function technicalDiagnostics(): iterable
    {
        yield 'source unavailable' => [AdministrativeActionDecisionContextTechnicalDiagnostic::SourceUnavailable];
        yield 'corrupted evidence' => [AdministrativeActionDecisionContextTechnicalDiagnostic::CorruptedEvidence];
    }

    /** @param class-string $class */
    #[DataProvider('immutableModels')]
    public function test_models_are_final_and_readonly(string $class): void
    {
        $reflection = new ReflectionClass($class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
    }

    /** @return iterable<string, array{class-string}> */
    public static function immutableModels(): iterable
    {
        yield 'authority' => [AdministrativeActionDecisionAuthority::class];
        yield 'context' => [AdministrativeActionDecisionContext::class];
        yield 'checksum' => [AdministrativeActionDecisionContextChecksum::class];
        yield 'resolution' => [AdministrativeActionDecisionContextResolution::class];
    }

    private function context(): AdministrativeActionDecisionContext
    {
        return new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::independentApprovalRequired(
                $this->actor('author-001'),
                $this->actor('decider-001'),
            ),
        );
    }

    private function actor(string $value): ActorId
    {
        return ActorId::fromString($value);
    }
}
