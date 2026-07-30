<?php

namespace Tests\Unit\Modules\Geography;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleCurrentState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleDecision;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleWorkflowTest extends TestCase
{
    #[DataProvider('stateActionMatrix')]
    public function test_complete_state_action_matrix(
        PlaceLifecycleState $state,
        PlaceLifecycleAction $action,
        PlaceLifecycleDecision $decision,
        PlaceLifecycleState $resultingState,
    ): void {
        $result = (new PlaceLifecycleWorkflow)->decide($this->current($state), $action, self::context());

        self::assertSame($decision, $result->decision);
        self::assertSame($resultingState, $result->state);

        if ($decision === PlaceLifecycleDecision::Applied) {
            self::assertNotNull($result->transition);
            self::assertSame($state, $result->transition->from);
            self::assertSame($action, $result->transition->action);
            self::assertSame($resultingState, $result->transition->to);
        } else {
            self::assertNull($result->transition);
        }
    }

    /** @return iterable<string, array{PlaceLifecycleState, PlaceLifecycleAction, PlaceLifecycleDecision, PlaceLifecycleState}> */
    public static function stateActionMatrix(): iterable
    {
        yield 'enabled enable' => [PlaceLifecycleState::Enabled, PlaceLifecycleAction::Enable, PlaceLifecycleDecision::AlreadyInState, PlaceLifecycleState::Enabled];
        yield 'enabled disable' => [PlaceLifecycleState::Enabled, PlaceLifecycleAction::Disable, PlaceLifecycleDecision::Applied, PlaceLifecycleState::Disabled];
        yield 'enabled merge' => [PlaceLifecycleState::Enabled, PlaceLifecycleAction::Merge, PlaceLifecycleDecision::Applied, PlaceLifecycleState::Merged];
        yield 'disabled enable' => [PlaceLifecycleState::Disabled, PlaceLifecycleAction::Enable, PlaceLifecycleDecision::Applied, PlaceLifecycleState::Enabled];
        yield 'disabled disable' => [PlaceLifecycleState::Disabled, PlaceLifecycleAction::Disable, PlaceLifecycleDecision::AlreadyInState, PlaceLifecycleState::Disabled];
        yield 'disabled merge' => [PlaceLifecycleState::Disabled, PlaceLifecycleAction::Merge, PlaceLifecycleDecision::Applied, PlaceLifecycleState::Merged];
        yield 'merged enable' => [PlaceLifecycleState::Merged, PlaceLifecycleAction::Enable, PlaceLifecycleDecision::TerminalState, PlaceLifecycleState::Merged];
        yield 'merged disable' => [PlaceLifecycleState::Merged, PlaceLifecycleAction::Disable, PlaceLifecycleDecision::TerminalState, PlaceLifecycleState::Merged];
        yield 'merged merge' => [PlaceLifecycleState::Merged, PlaceLifecycleAction::Merge, PlaceLifecycleDecision::TerminalState, PlaceLifecycleState::Merged];
    }

    #[DataProvider('mergeRefusalMatrix')]
    public function test_merge_refusals_are_derived_only_from_certified_context(
        PlaceMergeContextV1 $context,
        PlaceLifecycleDecision $decision,
    ): void {
        $result = (new PlaceLifecycleWorkflow)->decide(
            $this->current(PlaceLifecycleState::Enabled),
            PlaceLifecycleAction::Merge,
            $context,
        );

        self::assertSame($decision, $result->decision);
        self::assertSame(PlaceLifecycleState::Enabled, $result->state);
        self::assertNull($result->transition);
    }

    /** @return iterable<string, array{PlaceMergeContextV1, PlaceLifecycleDecision}> */
    public static function mergeRefusalMatrix(): iterable
    {
        $source = PlaceId::fromString('10000000-0000-4000-8000-000000000001');

        yield 'same identity' => [self::context(target: $source), PlaceLifecycleDecision::SameIdentity];
        yield 'target disabled' => [self::context(targetState: PlaceMergeObservedState::Disabled), PlaceLifecycleDecision::TargetDisabled];
        yield 'target merged' => [self::context(targetState: PlaceMergeObservedState::Merged), PlaceLifecycleDecision::TargetMerged];
        yield 'different type' => [self::context(targetType: PlaceType::Region), PlaceLifecycleDecision::DifferentType];
        yield 'different country' => [self::context(targetCountry: CountryCode::fromString('FR')), PlaceLifecycleDecision::DifferentCountry];
    }

    public function test_context_source_must_match_current_place(): void
    {
        $result = (new PlaceLifecycleWorkflow)->decide(
            new PlaceLifecycleCurrentState(
                PlaceId::fromString('10000000-0000-4000-8000-000000000099'),
                PlaceLifecycleState::Enabled,
            ),
            PlaceLifecycleAction::Merge,
            self::context(),
        );

        self::assertSame(PlaceLifecycleDecision::InvalidContext, $result->decision);
        self::assertSame(PlaceLifecycleState::Enabled, $result->state);
        self::assertNull($result->transition);
    }

    public function test_target_evidence_is_not_evaluated_for_enable_or_disable(): void
    {
        $context = self::context(
            targetState: PlaceMergeObservedState::Merged,
            targetType: PlaceType::Region,
            targetCountry: CountryCode::fromString('FR'),
        );
        $workflow = new PlaceLifecycleWorkflow;

        self::assertSame(
            PlaceLifecycleDecision::Applied,
            $workflow->decide($this->current(PlaceLifecycleState::Disabled), PlaceLifecycleAction::Enable, $context)->decision,
        );
        self::assertSame(
            PlaceLifecycleDecision::Applied,
            $workflow->decide($this->current(PlaceLifecycleState::Enabled), PlaceLifecycleAction::Disable, $context)->decision,
        );
    }

    public function test_initial_state_and_closed_enums_are_exact(): void
    {
        self::assertSame(PlaceLifecycleState::Enabled, (new PlaceLifecycleWorkflow)->initialState());
        self::assertSame(['enabled', 'disabled', 'merged'], array_column(PlaceLifecycleState::cases(), 'value'));
        self::assertSame(['enable', 'disable', 'merge'], array_column(PlaceLifecycleAction::cases(), 'value'));
        self::assertSame(
            ['applied', 'already_in_state', 'terminal_state', 'same_identity', 'target_disabled', 'target_merged', 'different_type', 'different_country', 'invalid_context'],
            array_column(PlaceLifecycleDecision::cases(), 'value'),
        );
    }

    private function current(PlaceLifecycleState $state): PlaceLifecycleCurrentState
    {
        return new PlaceLifecycleCurrentState(
            PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            $state,
        );
    }

    private static function context(
        ?PlaceId $target = null,
        PlaceMergeObservedState $targetState = PlaceMergeObservedState::Enabled,
        PlaceType $targetType = PlaceType::City,
        ?CountryCode $targetCountry = null,
    ): PlaceMergeContextV1 {
        return new PlaceMergeContextV1(
            sourceId: PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            targetId: $target ?? PlaceId::fromString('10000000-0000-4000-8000-000000000002'),
            expectedSourceVersion: new PlaceMergeExpectedSourceVersion(7),
            observedTargetVersion: new PlaceMergeObservedTargetVersion(11),
            observedTargetState: $targetState,
            observedSourceType: PlaceType::City,
            observedTargetType: $targetType,
            observedSourceCountry: CountryCode::fromString('SN'),
            observedTargetCountry: $targetCountry ?? CountryCode::fromString('SN'),
            actor: PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
            occurredAt: PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
            intentId: PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
        );
    }
}
