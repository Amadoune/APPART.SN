<?php

namespace Tests\Unit\AdministrativeActionPersistenceCoexistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleSourceChecksum;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceCoexistenceContract;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceCoexistenceRule;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceOperation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceOwner;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceWriteMode;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AdministrativeActionPersistenceCoexistenceContractTest extends TestCase
{
    #[DataProvider('responsibilityMatrix')]
    public function test_each_operation_has_one_authority_and_no_fallback(
        AdministrativeActionPersistenceOperation $operation,
        AdministrativeActionPersistenceOwner $owner,
        AdministrativeActionPersistenceWriteMode $writeMode,
    ): void {
        $rule = (new AdministrativeActionPersistenceCoexistenceContract)->ruleFor($operation);

        self::assertSame($operation, $rule->operation);
        self::assertSame($owner, $rule->authoritativeOwner);
        self::assertSame($writeMode, $rule->writeMode);
        self::assertFalse($rule->fallbackAllowed);
    }

    public function test_the_matrix_covers_every_operation_exactly_once(): void
    {
        $rules = (new AdministrativeActionPersistenceCoexistenceContract)->rules();
        self::assertCount(count(AdministrativeActionPersistenceOperation::cases()), $rules);
        self::assertSame(
            array_column(AdministrativeActionPersistenceOperation::cases(), 'value'),
            array_column(array_map(static fn (AdministrativeActionPersistenceCoexistenceRule $rule): array => [
                'value' => $rule->operation->value,
            ], $rules), 'value'),
        );
    }

    public function test_lifecycle_transition_is_the_only_atomic_dual_write(): void
    {
        $rules = array_filter(
            (new AdministrativeActionPersistenceCoexistenceContract)->rules(),
            static fn (AdministrativeActionPersistenceCoexistenceRule $rule): bool => $rule->writeMode === AdministrativeActionPersistenceWriteMode::AtomicHistoricalAndLifecycle,
        );

        self::assertCount(1, $rules);
        self::assertSame(
            AdministrativeActionPersistenceOperation::LifecycleTransition,
            array_values($rules)[0]->operation,
        );
    }

    public function test_enrollment_checkpoint_is_exact_and_immutable(): void
    {
        $checkpoint = new AdministrativeActionLifecycleEnrollmentCheckpoint(
            AdministrativeActionId::fromString('a470b100-0000-4000-8000-000000000001'),
            4,
            AdministrativeActionLifecycleState::PendingApproval,
            AdministrativeActionLifecycleSourceChecksum::fromString(str_repeat('a', 64)),
        );

        self::assertSame(4, $checkpoint->historicalVersion);
        self::assertSame(AdministrativeActionLifecycleState::PendingApproval, $checkpoint->state);
        self::assertTrue((new ReflectionClass($checkpoint))->isReadOnly());
    }

    public function test_enrollment_accepts_the_historical_initial_version(): void
    {
        $checkpoint = new AdministrativeActionLifecycleEnrollmentCheckpoint(
            AdministrativeActionId::fromString('a470b100-0000-4000-8000-000000000001'),
            0,
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleSourceChecksum::fromString(str_repeat('b', 64)),
        );

        self::assertSame(0, $checkpoint->historicalVersion);
    }

    public function test_negative_historical_version_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AdministrativeActionLifecycleEnrollmentCheckpoint(
            AdministrativeActionId::fromString('a470b100-0000-4000-8000-000000000001'),
            -1,
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleSourceChecksum::fromString(str_repeat('c', 64)),
        );
    }

    public function test_invalid_source_checksum_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AdministrativeActionLifecycleSourceChecksum::fromString('invalid');
    }

    public function test_enrollment_results_are_closed(): void
    {
        self::assertSame([
            'enrolled',
            'already_enrolled',
            'source_missing',
            'source_unavailable',
            'source_corrupted',
            'enrollment_divergence',
            'persistence_corrupted',
        ], array_column(AdministrativeActionLifecycleEnrollmentResult::cases(), 'value'));
    }

    /** @return iterable<string, array{AdministrativeActionPersistenceOperation, AdministrativeActionPersistenceOwner, AdministrativeActionPersistenceWriteMode}> */
    public static function responsibilityMatrix(): iterable
    {
        yield 'creation' => [
            AdministrativeActionPersistenceOperation::Creation,
            AdministrativeActionPersistenceOwner::HistoricalRegistry,
            AdministrativeActionPersistenceWriteMode::HistoricalOnly,
        ];
        yield 'reason mutation' => [
            AdministrativeActionPersistenceOperation::ReasonMutation,
            AdministrativeActionPersistenceOwner::HistoricalRegistry,
            AdministrativeActionPersistenceWriteMode::HistoricalOnly,
        ];
        yield 'audit details' => [
            AdministrativeActionPersistenceOperation::AuditDetailMutation,
            AdministrativeActionPersistenceOwner::HistoricalRegistry,
            AdministrativeActionPersistenceWriteMode::HistoricalOnly,
        ];
        yield 'enrollment' => [
            AdministrativeActionPersistenceOperation::LifecycleEnrollment,
            AdministrativeActionPersistenceOwner::LifecycleJournal,
            AdministrativeActionPersistenceWriteMode::LifecycleOnly,
        ];
        yield 'lifecycle read' => [
            AdministrativeActionPersistenceOperation::LifecycleRead,
            AdministrativeActionPersistenceOwner::LifecycleJournal,
            AdministrativeActionPersistenceWriteMode::ReadOnly,
        ];
        yield 'transition' => [
            AdministrativeActionPersistenceOperation::LifecycleTransition,
            AdministrativeActionPersistenceOwner::LifecycleJournal,
            AdministrativeActionPersistenceWriteMode::AtomicHistoricalAndLifecycle,
        ];
        yield 'compatibility read' => [
            AdministrativeActionPersistenceOperation::CompatibilityRead,
            AdministrativeActionPersistenceOwner::HistoricalRegistry,
            AdministrativeActionPersistenceWriteMode::ReadOnly,
        ];
    }
}
