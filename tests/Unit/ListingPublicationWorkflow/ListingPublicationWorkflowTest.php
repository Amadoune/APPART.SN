<?php

namespace Tests\Unit\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDecision;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDecisionStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDiagnostic;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ListingPublicationWorkflowTest extends TestCase
{
    /** @return iterable<string, array{ListingPublicationState, ListingPublicationAction, ListingPublicationState}> */
    public static function allowedTransitions(): iterable
    {
        $matrix = [
            'draft' => ['submit' => 'submitted', 'withdraw' => 'withdrawn', 'archive' => 'archived'],
            'submitted' => ['begin_review' => 'under_review', 'withdraw' => 'withdrawn'],
            'under_review' => ['approve_and_publish' => 'published', 'request_changes' => 'changes_requested', 'reject' => 'rejected', 'withdraw' => 'withdrawn'],
            'changes_requested' => ['submit' => 'submitted', 'withdraw' => 'withdrawn', 'archive' => 'archived'],
            'published' => ['review_material_change' => 'under_review', 'suspend' => 'suspended', 'expire' => 'expired', 'withdraw' => 'withdrawn'],
            'suspended' => ['reinstate' => 'published', 'request_changes' => 'changes_requested', 'reject' => 'rejected', 'archive' => 'archived'],
            'expired' => ['review_renewal' => 'under_review', 'renew_directly' => 'published', 'withdraw' => 'withdrawn', 'archive' => 'archived'],
            'withdrawn' => ['approve_republication' => 'under_review', 'archive' => 'archived'],
            'rejected' => ['archive' => 'archived'],
        ];
        foreach ($matrix as $from => $transitions) {
            foreach ($transitions as $action => $to) {
                yield $from.'>'.$action => [ListingPublicationState::from($from), ListingPublicationAction::from($action), ListingPublicationState::from($to)];
            }
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_each_normative_transition_is_allowed(
        ListingPublicationState $from,
        ListingPublicationAction $action,
        ListingPublicationState $to,
    ): void {
        $decision = (new ListingPublicationWorkflow)->decide($from, $action);

        self::assertSame(ListingPublicationDecisionStatus::Allowed, $decision->status);
        self::assertEquals(new ListingPublicationTransition($from, $to, $action), $decision->transition);
        self::assertNull($decision->diagnostic);
    }

    public function test_exactly_twenty_seven_transitions_are_allowed_and_every_other_pair_is_denied(): void
    {
        $workflow = new ListingPublicationWorkflow;
        $allowed = 0;
        $denied = 0;
        foreach (ListingPublicationState::cases() as $state) {
            foreach (ListingPublicationAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->status === ListingPublicationDecisionStatus::Allowed) {
                    $allowed++;
                } else {
                    $denied++;
                    self::assertNull($decision->transition);
                    self::assertNotNull($decision->diagnostic);
                }
            }
        }

        self::assertSame(27, $allowed);
        self::assertSame(123, $denied);
    }

    public function test_denial_diagnostics_are_deterministic_and_specific(): void
    {
        $workflow = new ListingPublicationWorkflow;

        self::assertSame(ListingPublicationDiagnosticCode::UnknownAction, $workflow->decide(ListingPublicationState::Draft, ListingPublicationAction::Unknown)->diagnostic?->code);
        self::assertSame(ListingPublicationDiagnosticCode::TerminalState, $workflow->decide(ListingPublicationState::Archived, ListingPublicationAction::Submit)->diagnostic?->code);
        self::assertSame(ListingPublicationDiagnosticCode::IncompatibleState, $workflow->decide(ListingPublicationState::Published, ListingPublicationAction::Reinstate)->diagnostic?->code);
        self::assertSame(ListingPublicationDiagnosticCode::TransitionForbidden, $workflow->decide(ListingPublicationState::Draft, ListingPublicationAction::Expire)->diagnostic?->code);
    }

    public function test_same_input_always_returns_the_same_decision(): void
    {
        $workflow = new ListingPublicationWorkflow;
        foreach (ListingPublicationState::cases() as $state) {
            foreach (ListingPublicationAction::cases() as $action) {
                self::assertEquals($workflow->decide($state, $action), $workflow->decide($state, $action));
            }
        }
    }

    public function test_state_action_status_and_diagnostic_sets_are_closed(): void
    {
        self::assertSame(['draft', 'submitted', 'under_review', 'changes_requested', 'published', 'suspended', 'expired', 'withdrawn', 'rejected', 'archived'], array_column(ListingPublicationState::cases(), 'value'));
        self::assertSame(['submit', 'begin_review', 'approve_and_publish', 'request_changes', 'reject', 'withdraw', 'review_material_change', 'suspend', 'expire', 'reinstate', 'review_renewal', 'renew_directly', 'approve_republication', 'archive', 'unknown'], array_column(ListingPublicationAction::cases(), 'value'));
        self::assertSame(['allowed', 'denied'], array_column(ListingPublicationDecisionStatus::cases(), 'value'));
        self::assertSame(['transition_forbidden', 'incompatible_state', 'terminal_state', 'unknown_action', 'workflow_corrupted'], array_column(ListingPublicationDiagnosticCode::cases(), 'value'));
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
        yield [ListingPublicationWorkflow::class];
        yield [ListingPublicationTransition::class];
        yield [ListingPublicationDecision::class];
        yield [ListingPublicationDiagnostic::class];
    }
}
