<?php

namespace Tests\Unit\ProfessionalStatusTransitionContext;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextChecksum;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppendInspection;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayOutcome;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ProfessionalStatusTransitionContextContractTest extends TestCase
{
    public function test_context_is_explicit_immutable_and_versions_the_future_append(): void
    {
        $context = $this->context();

        self::assertSame(1, $context->expectedVersion->value);
        self::assertSame(2, $context->expectedVersion->next());
        self::assertSame('2026-07-22T12:00:00.000000Z', $context->occurredAt->canonical());

        foreach ([$context, $context->actor, $context->occurredAt, $context->expectedVersion] as $model) {
            self::assertTrue((new ReflectionClass($model))->isReadOnly());
        }
    }

    #[DataProvider('invalidContextValues')]
    public function test_context_values_reject_implicit_or_invalid_input(callable $factory): void
    {
        $this->expectException(InvalidArgumentException::class);
        $factory();
    }

    public function test_inspection_results_are_closed_and_snapshot_shape_is_strict(): void
    {
        $snapshot = $this->snapshot();
        $found = ProfessionalStatusContextualInspectionResult::found($snapshot);

        self::assertSame(['found', 'missing', 'corrupted'], array_column(ProfessionalStatusContextualInspectionStatus::cases(), 'value'));
        self::assertSame(ProfessionalStatusContextualInspectionStatus::Found, $found->status);
        self::assertSame($snapshot, $found->snapshot);
        self::assertSame(2, $found->snapshot?->version);
        self::assertTrue((new ReflectionClass($snapshot))->isReadOnly());

        foreach ([ProfessionalStatusContextualInspectionResult::missing($this->id()), ProfessionalStatusContextualInspectionResult::corrupted($this->id())] as $result) {
            self::assertNull($result->snapshot);
        }
    }

    public function test_replay_policy_compares_only_the_exact_inspected_append(): void
    {
        $policy = new ProfessionalStatusReplayPolicy;

        self::assertSame(ProfessionalStatusReplayOutcome::AlreadyApplied, $policy->classify(ProfessionalStatusAction::Suspend, $this->context(), $this->snapshot()));

        $differentActor = new ProfessionalStatusTransitionContext(
            ProfessionalStatusActorId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
            $this->occurredAt(),
            new ProfessionalStatusExpectedVersion(1),
        );
        self::assertSame(ProfessionalStatusReplayOutcome::ContextDivergence, $policy->classify(ProfessionalStatusAction::Suspend, $differentActor, $this->snapshot()));

        self::assertSame(ProfessionalStatusReplayOutcome::Conflict, $policy->classify(ProfessionalStatusAction::Reactivate, $this->context(), $this->snapshot()));
        $wrongVersion = new ProfessionalStatusTransitionContext($this->context()->actor, $this->occurredAt(), new ProfessionalStatusExpectedVersion(2));
        self::assertSame(ProfessionalStatusReplayOutcome::Conflict, $policy->classify(ProfessionalStatusAction::Suspend, $wrongVersion, $this->snapshot()));
        self::assertSame(['already_applied', 'context_divergence', 'conflict'], array_column(ProfessionalStatusReplayOutcome::cases(), 'value'));
    }

    public static function invalidContextValues(): array
    {
        return [
            'actor' => [static fn () => ProfessionalStatusActorId::fromString('implicit')],
            'non utc' => [static fn () => ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T14:00:00+02:00'))],
            'version zero' => [static fn () => new ProfessionalStatusExpectedVersion(0)],
            'checksum' => [static fn () => ProfessionalStatusContextChecksum::fromString('invalid')],
        ];
    }

    private function context(): ProfessionalStatusTransitionContext
    {
        return new ProfessionalStatusTransitionContext(
            ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            $this->occurredAt(),
            new ProfessionalStatusExpectedVersion(1),
        );
    }

    private function snapshot(): ProfessionalStatusContextualAppendInspection
    {
        return new ProfessionalStatusContextualAppendInspection(
            $this->id(),
            2,
            $this->transition(),
            $this->context()->actor,
            $this->occurredAt(),
            ProfessionalStatusContextChecksum::fromString(str_repeat('a', 64)),
        );
    }

    private function transition(): ProfessionalStatusTransition
    {
        return new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
    }

    private function occurredAt(): ProfessionalStatusOccurredAt
    {
        return ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
    }

    private function id(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000001');
    }
}
