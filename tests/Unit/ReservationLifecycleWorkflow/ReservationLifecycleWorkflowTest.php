<?php

namespace Tests\Unit\ReservationLifecycleWorkflow;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleDiagnostic;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleDiagnosticCode;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflowResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReservationLifecycleWorkflowTest extends TestCase
{
    /** @return iterable<string, array{ReservationLifecycleState,ReservationLifecycleAction,ReservationLifecycleState}> */
    public static function allowedTransitions(): iterable
    {
        $matrix = [
            'draft' => ['submit' => 'requested', 'cancel' => 'cancelled'],
            'requested' => ['confirm' => 'confirmed', 'reject' => 'rejected', 'cancel' => 'cancelled', 'expire' => 'expired'],
            'confirmed' => ['start' => 'in_progress', 'cancel' => 'cancelled', 'expire' => 'expired'],
            'in_progress' => ['complete' => 'completed', 'cancel' => 'cancelled'],
        ];
        foreach ($matrix as $from => $transitions) {
            foreach ($transitions as $action => $to) {
                yield $from.'>'.$action => [ReservationLifecycleState::from($from), ReservationLifecycleAction::from($action), ReservationLifecycleState::from($to)];
            }
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_each_normative_transition_is_allowed(ReservationLifecycleState $from, ReservationLifecycleAction $action, ReservationLifecycleState $to): void
    {
        $decision = (new ReservationLifecycleWorkflow)->decide($from, $action);

        self::assertSame(ReservationLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertEquals(new ReservationLifecycleTransition($from, $to, $action), $decision->transition);
        self::assertNull($decision->diagnostic);
    }

    public function test_exactly_eleven_transitions_are_allowed_and_all_other_pairs_are_denied(): void
    {
        $workflow = new ReservationLifecycleWorkflow;
        $allowed = 0;
        $denied = 0;
        foreach (ReservationLifecycleState::cases() as $state) {
            foreach (ReservationLifecycleAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->result === ReservationLifecycleWorkflowResult::Allowed) {
                    $allowed++;
                    self::assertNotNull($decision->transition);
                    self::assertNull($decision->diagnostic);
                } else {
                    $denied++;
                    self::assertNull($decision->transition);
                    self::assertNotNull($decision->diagnostic);
                }
            }
        }

        self::assertSame(11, $allowed);
        self::assertSame(53, $denied);
    }

    public function test_denial_diagnostics_are_exact_and_prioritized(): void
    {
        $workflow = new ReservationLifecycleWorkflow;

        self::assertSame(ReservationLifecycleDiagnosticCode::UnknownAction, $workflow->decide(ReservationLifecycleState::Draft, ReservationLifecycleAction::Unknown)->diagnostic?->code);
        self::assertSame(ReservationLifecycleDiagnosticCode::UnknownAction, $workflow->decide(ReservationLifecycleState::Completed, ReservationLifecycleAction::Unknown)->diagnostic?->code);
        self::assertSame(ReservationLifecycleDiagnosticCode::TerminalState, $workflow->decide(ReservationLifecycleState::Completed, ReservationLifecycleAction::Complete)->diagnostic?->code);
        self::assertSame(ReservationLifecycleDiagnosticCode::TerminalState, $workflow->decide(ReservationLifecycleState::Cancelled, ReservationLifecycleAction::Submit)->diagnostic?->code);
        self::assertSame(ReservationLifecycleDiagnosticCode::IncompatibleState, $workflow->decide(ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit)->diagnostic?->code);
        self::assertSame(ReservationLifecycleDiagnosticCode::TransitionForbidden, $workflow->decide(ReservationLifecycleState::Draft, ReservationLifecycleAction::Confirm)->diagnostic?->code);
    }

    public function test_every_state_action_pair_is_deterministic(): void
    {
        $workflow = new ReservationLifecycleWorkflow;
        foreach (ReservationLifecycleState::cases() as $state) {
            foreach (ReservationLifecycleAction::cases() as $action) {
                self::assertEquals($workflow->decide($state, $action), $workflow->decide($state, $action));
            }
        }
    }

    public function test_all_contract_sets_are_closed_and_exhaustive(): void
    {
        self::assertSame(['draft', 'requested', 'confirmed', 'in_progress', 'completed', 'cancelled', 'expired', 'rejected'], array_column(ReservationLifecycleState::cases(), 'value'));
        self::assertSame(['submit', 'confirm', 'reject', 'start', 'complete', 'cancel', 'expire', 'unknown'], array_column(ReservationLifecycleAction::cases(), 'value'));
        self::assertSame(['allowed', 'denied'], array_column(ReservationLifecycleWorkflowResult::cases(), 'value'));
        self::assertSame(['transition_forbidden', 'incompatible_state', 'terminal_state', 'unknown_action', 'workflow_corrupted'], array_column(ReservationLifecycleDiagnosticCode::cases(), 'value'));
    }

    /** @param class-string $class */
    #[DataProvider('immutableModels')]
    public function test_models_are_final_and_readonly(string $class): void
    {
        $reflection = new ReflectionClass($class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
    }

    /** @return iterable<array{class-string}> */
    public static function immutableModels(): iterable
    {
        yield [ReservationLifecycleWorkflow::class];
        yield [ReservationLifecycleTransition::class];
        yield [ReservationLifecycleDecision::class];
        yield [ReservationLifecycleDiagnostic::class];
    }
}
