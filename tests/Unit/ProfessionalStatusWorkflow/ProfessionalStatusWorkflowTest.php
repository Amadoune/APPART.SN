<?php

namespace Tests\Unit\ProfessionalStatusWorkflow;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusDecision;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusDiagnostic;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflowResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ProfessionalStatusWorkflowTest extends TestCase
{
    /** @return iterable<string, array{ProfessionalStatusState,ProfessionalStatusAction,ProfessionalStatusState}> */
    public static function allowedTransitions(): iterable
    {
        yield 'suspend' => [ProfessionalStatusState::Active, ProfessionalStatusAction::Suspend, ProfessionalStatusState::Suspended];
        yield 'reactivate' => [ProfessionalStatusState::Suspended, ProfessionalStatusAction::Reactivate, ProfessionalStatusState::Active];
    }

    #[DataProvider('allowedTransitions')]
    public function test_certified_transitions_are_allowed(ProfessionalStatusState $from, ProfessionalStatusAction $action, ProfessionalStatusState $to): void
    {
        $decision = (new ProfessionalStatusWorkflow)->decide($from, $action);

        self::assertSame(ProfessionalStatusWorkflowResult::Allowed, $decision->result);
        self::assertEquals(new ProfessionalStatusTransition($from, $to, $action), $decision->transition);
        self::assertNull($decision->diagnostic);
    }

    public function test_initial_state_is_explicitly_active(): void
    {
        self::assertSame(ProfessionalStatusState::Active, (new ProfessionalStatusWorkflow)->initialState());
    }

    public function test_all_six_state_action_pairs_are_exhaustively_classified(): void
    {
        $workflow = new ProfessionalStatusWorkflow;
        $allowed = 0;
        $denied = 0;

        foreach (ProfessionalStatusState::cases() as $state) {
            foreach (ProfessionalStatusAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->result === ProfessionalStatusWorkflowResult::Allowed) {
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
        self::assertSame(4, $denied);
    }

    public function test_denials_are_explicit_and_prioritized(): void
    {
        $workflow = new ProfessionalStatusWorkflow;

        self::assertSame(ProfessionalStatusDiagnostic::IncompatibleState, $workflow->decide(ProfessionalStatusState::Active, ProfessionalStatusAction::Reactivate)->diagnostic);
        self::assertSame(ProfessionalStatusDiagnostic::IncompatibleState, $workflow->decide(ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend)->diagnostic);
        self::assertSame(ProfessionalStatusDiagnostic::UnknownAction, $workflow->decide(ProfessionalStatusState::Active, ProfessionalStatusAction::Unknown)->diagnostic);
        self::assertSame(ProfessionalStatusDiagnostic::UnknownAction, $workflow->decide(ProfessionalStatusState::Suspended, ProfessionalStatusAction::Unknown)->diagnostic);
    }

    public function test_every_decision_is_deterministic(): void
    {
        $workflow = new ProfessionalStatusWorkflow;
        foreach (ProfessionalStatusState::cases() as $state) {
            foreach (ProfessionalStatusAction::cases() as $action) {
                self::assertEquals($workflow->decide($state, $action), $workflow->decide($state, $action));
            }
        }
    }

    public function test_contract_sets_are_closed(): void
    {
        self::assertSame(['active', 'suspended'], array_column(ProfessionalStatusState::cases(), 'value'));
        self::assertSame(['suspend', 'reactivate', 'unknown'], array_column(ProfessionalStatusAction::cases(), 'value'));
        self::assertSame(['allowed', 'denied'], array_column(ProfessionalStatusWorkflowResult::cases(), 'value'));
        self::assertSame(['incompatible_state', 'unknown_action'], array_column(ProfessionalStatusDiagnostic::cases(), 'value'));
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
        yield [ProfessionalStatusWorkflow::class];
        yield [ProfessionalStatusTransition::class];
        yield [ProfessionalStatusDecision::class];
    }
}
