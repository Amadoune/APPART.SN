<?php

namespace Tests\Unit\LeadLifecycleWorkflow;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleDecision;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleDiagnostic;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflowResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LeadLifecycleWorkflowTest extends TestCase
{
    /** @return iterable<string, array{LeadLifecycleState, LeadLifecycleAction, LeadLifecycleState}> */
    public static function allowedTransitions(): iterable
    {
        yield 'created>deliver' => [LeadLifecycleState::Created, LeadLifecycleAction::Deliver, LeadLifecycleState::Delivered];
        yield 'created>reject' => [LeadLifecycleState::Created, LeadLifecycleAction::Reject, LeadLifecycleState::Rejected];
        yield 'delivered>close' => [LeadLifecycleState::Delivered, LeadLifecycleAction::Close, LeadLifecycleState::Closed];
        yield 'rejected>close' => [LeadLifecycleState::Rejected, LeadLifecycleAction::Close, LeadLifecycleState::Closed];
    }

    #[DataProvider('allowedTransitions')]
    public function test_each_normative_transition_is_allowed(
        LeadLifecycleState $from,
        LeadLifecycleAction $action,
        LeadLifecycleState $to,
    ): void {
        $decision = (new LeadLifecycleWorkflow)->decide($from, $action);

        self::assertSame(LeadLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertEquals(new LeadLifecycleTransition($from, $to, $action), $decision->transition);
        self::assertNull($decision->diagnostic);
    }

    public function test_creation_is_the_explicit_entry_of_the_cycle(): void
    {
        self::assertSame(LeadLifecycleState::Created, (new LeadLifecycleWorkflow)->initialState());
    }

    public function test_all_sixteen_state_action_pairs_are_exhaustively_classified(): void
    {
        $workflow = new LeadLifecycleWorkflow;
        $allowed = 0;
        $denied = 0;

        foreach (LeadLifecycleState::cases() as $state) {
            foreach (LeadLifecycleAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->result === LeadLifecycleWorkflowResult::Allowed) {
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

        self::assertSame(4, $allowed);
        self::assertSame(12, $denied);
    }

    public function test_denial_diagnostics_are_exact_and_prioritized(): void
    {
        $workflow = new LeadLifecycleWorkflow;

        self::assertSame(LeadLifecycleDiagnostic::UnknownAction, $workflow->decide(LeadLifecycleState::Created, LeadLifecycleAction::Unknown)->diagnostic);
        self::assertSame(LeadLifecycleDiagnostic::UnknownAction, $workflow->decide(LeadLifecycleState::Closed, LeadLifecycleAction::Unknown)->diagnostic);
        self::assertSame(LeadLifecycleDiagnostic::TerminalState, $workflow->decide(LeadLifecycleState::Closed, LeadLifecycleAction::Close)->diagnostic);
        self::assertSame(LeadLifecycleDiagnostic::IncompatibleState, $workflow->decide(LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver)->diagnostic);
        self::assertSame(LeadLifecycleDiagnostic::TransitionForbidden, $workflow->decide(LeadLifecycleState::Created, LeadLifecycleAction::Close)->diagnostic);
    }

    public function test_every_decision_is_deterministic(): void
    {
        $workflow = new LeadLifecycleWorkflow;
        foreach (LeadLifecycleState::cases() as $state) {
            foreach (LeadLifecycleAction::cases() as $action) {
                self::assertEquals($workflow->decide($state, $action), $workflow->decide($state, $action));
            }
        }
    }

    public function test_contract_sets_are_closed_and_exhaustive(): void
    {
        self::assertSame(['created', 'delivered', 'rejected', 'closed'], array_column(LeadLifecycleState::cases(), 'value'));
        self::assertSame(['deliver', 'reject', 'close', 'unknown'], array_column(LeadLifecycleAction::cases(), 'value'));
        self::assertSame(['allowed', 'denied'], array_column(LeadLifecycleWorkflowResult::cases(), 'value'));
        self::assertSame(['transition_forbidden', 'incompatible_state', 'terminal_state', 'unknown_action'], array_column(LeadLifecycleDiagnostic::cases(), 'value'));
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
        yield [LeadLifecycleWorkflow::class];
        yield [LeadLifecycleTransition::class];
        yield [LeadLifecycleDecision::class];
    }
}
