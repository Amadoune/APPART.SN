<?php

namespace Tests\Unit\MediaItemLifecycleWorkflow;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleDecision;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleDiagnostic;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflowResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MediaItemLifecycleWorkflowTest extends TestCase
{
    /** @return iterable<string, array{MediaItemLifecycleAction,MediaItemLifecycleState}> */
    public static function allowedTransitions(): iterable
    {
        yield 'remove' => [MediaItemLifecycleAction::Remove, MediaItemLifecycleState::Removed];
        yield 'archive' => [MediaItemLifecycleAction::Archive, MediaItemLifecycleState::Archived];
    }

    #[DataProvider('allowedTransitions')]
    public function test_only_the_two_certified_transitions_are_allowed(MediaItemLifecycleAction $action, MediaItemLifecycleState $to): void
    {
        $decision = (new MediaItemLifecycleWorkflow)->decide(MediaItemLifecycleState::Active, $action);

        self::assertSame(MediaItemLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertEquals(new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, $to, $action), $decision->transition);
        self::assertNull($decision->diagnostic);
    }

    public function test_creation_is_outside_the_workflow_and_initial_state_is_active(): void
    {
        self::assertSame(MediaItemLifecycleState::Active, (new MediaItemLifecycleWorkflow)->initialState());
        self::assertSame(['remove', 'archive', 'unknown'], array_column(MediaItemLifecycleAction::cases(), 'value'));
    }

    public function test_all_nine_state_action_pairs_are_exhaustively_classified(): void
    {
        $workflow = new MediaItemLifecycleWorkflow;
        $allowed = 0;
        $denied = 0;

        foreach (MediaItemLifecycleState::cases() as $state) {
            foreach (MediaItemLifecycleAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->result === MediaItemLifecycleWorkflowResult::Allowed) {
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

        self::assertSame(2, $allowed);
        self::assertSame(7, $denied);
    }

    public function test_terminal_states_and_unknown_actions_have_prioritized_diagnostics(): void
    {
        $workflow = new MediaItemLifecycleWorkflow;
        foreach ([MediaItemLifecycleState::Removed, MediaItemLifecycleState::Archived] as $terminal) {
            self::assertSame(MediaItemLifecycleDiagnostic::TerminalState, $workflow->decide($terminal, MediaItemLifecycleAction::Remove)->diagnostic);
            self::assertSame(MediaItemLifecycleDiagnostic::TerminalState, $workflow->decide($terminal, MediaItemLifecycleAction::Archive)->diagnostic);
            self::assertSame(MediaItemLifecycleDiagnostic::UnknownAction, $workflow->decide($terminal, MediaItemLifecycleAction::Unknown)->diagnostic);
        }
        self::assertSame(MediaItemLifecycleDiagnostic::UnknownAction, $workflow->decide(MediaItemLifecycleState::Active, MediaItemLifecycleAction::Unknown)->diagnostic);
    }

    public function test_every_decision_is_deterministic(): void
    {
        $workflow = new MediaItemLifecycleWorkflow;
        foreach (MediaItemLifecycleState::cases() as $state) {
            foreach (MediaItemLifecycleAction::cases() as $action) {
                self::assertEquals($workflow->decide($state, $action), $workflow->decide($state, $action));
            }
        }
    }

    public function test_contract_sets_are_closed(): void
    {
        self::assertSame(['active', 'removed', 'archived'], array_column(MediaItemLifecycleState::cases(), 'value'));
        self::assertSame(['remove', 'archive', 'unknown'], array_column(MediaItemLifecycleAction::cases(), 'value'));
        self::assertSame(['allowed', 'denied'], array_column(MediaItemLifecycleWorkflowResult::cases(), 'value'));
        self::assertSame(['terminal_state', 'unknown_action'], array_column(MediaItemLifecycleDiagnostic::cases(), 'value'));
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
        yield [MediaItemLifecycleWorkflow::class];
        yield [MediaItemLifecycleTransition::class];
        yield [MediaItemLifecycleDecision::class];
    }
}
