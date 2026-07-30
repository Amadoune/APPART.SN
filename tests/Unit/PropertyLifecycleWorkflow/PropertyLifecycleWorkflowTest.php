<?php

namespace Tests\Unit\PropertyLifecycleWorkflow;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleDecision;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleDiagnostic;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleDiagnosticCode;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflowResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PropertyLifecycleWorkflowTest extends TestCase
{
    /** @return iterable<string, array{PropertyLifecycleState, PropertyLifecycleAction, PropertyLifecycleState}> */
    public static function allowedTransitions(): iterable
    {
        $matrix = [
            'draft' => ['activate' => 'active', 'archive' => 'archived'],
            'active' => ['begin_maintenance' => 'under_maintenance', 'mark_unavailable' => 'unavailable', 'decommission' => 'decommissioned'],
            'under_maintenance' => ['complete_maintenance' => 'active', 'mark_unavailable' => 'unavailable', 'decommission' => 'decommissioned'],
            'unavailable' => ['restore_availability' => 'active', 'begin_maintenance' => 'under_maintenance', 'decommission' => 'decommissioned'],
            'decommissioned' => ['archive' => 'archived'],
        ];
        foreach ($matrix as $from => $transitions) {
            foreach ($transitions as $action => $to) {
                yield $from.'>'.$action => [PropertyLifecycleState::from($from), PropertyLifecycleAction::from($action), PropertyLifecycleState::from($to)];
            }
        }
    }

    #[DataProvider('allowedTransitions')]
    public function test_each_normative_transition_is_allowed(PropertyLifecycleState $from, PropertyLifecycleAction $action, PropertyLifecycleState $to): void
    {
        $decision = (new PropertyLifecycleWorkflow)->decide($from, $action);

        self::assertSame(PropertyLifecycleWorkflowResult::Allowed, $decision->result);
        self::assertEquals(new PropertyLifecycleTransition($from, $to, $action), $decision->transition);
        self::assertNull($decision->diagnostic);
    }

    public function test_exactly_twelve_transitions_are_allowed_and_all_other_pairs_are_denied(): void
    {
        $workflow = new PropertyLifecycleWorkflow;
        $allowed = 0;
        $denied = 0;
        foreach (PropertyLifecycleState::cases() as $state) {
            foreach (PropertyLifecycleAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->result === PropertyLifecycleWorkflowResult::Allowed) {
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

        self::assertSame(12, $allowed);
        self::assertSame(36, $denied);
    }

    public function test_denial_diagnostics_are_exact_and_prioritized(): void
    {
        $workflow = new PropertyLifecycleWorkflow;

        self::assertSame(PropertyLifecycleDiagnosticCode::UnknownAction, $workflow->decide(PropertyLifecycleState::Draft, PropertyLifecycleAction::Unknown)->diagnostic?->code);
        self::assertSame(PropertyLifecycleDiagnosticCode::UnknownAction, $workflow->decide(PropertyLifecycleState::Archived, PropertyLifecycleAction::Unknown)->diagnostic?->code);
        self::assertSame(PropertyLifecycleDiagnosticCode::TerminalState, $workflow->decide(PropertyLifecycleState::Archived, PropertyLifecycleAction::Activate)->diagnostic?->code);
        self::assertSame(PropertyLifecycleDiagnosticCode::IncompatibleState, $workflow->decide(PropertyLifecycleState::Active, PropertyLifecycleAction::Activate)->diagnostic?->code);
        self::assertSame(PropertyLifecycleDiagnosticCode::TransitionForbidden, $workflow->decide(PropertyLifecycleState::Draft, PropertyLifecycleAction::Decommission)->diagnostic?->code);
    }

    public function test_every_state_action_pair_is_deterministic(): void
    {
        $workflow = new PropertyLifecycleWorkflow;
        foreach (PropertyLifecycleState::cases() as $state) {
            foreach (PropertyLifecycleAction::cases() as $action) {
                self::assertEquals($workflow->decide($state, $action), $workflow->decide($state, $action));
            }
        }
    }

    public function test_all_contract_sets_are_closed_and_exhaustive(): void
    {
        self::assertSame(['draft', 'active', 'under_maintenance', 'unavailable', 'decommissioned', 'archived'], array_column(PropertyLifecycleState::cases(), 'value'));
        self::assertSame(['activate', 'begin_maintenance', 'complete_maintenance', 'mark_unavailable', 'restore_availability', 'decommission', 'archive', 'unknown'], array_column(PropertyLifecycleAction::cases(), 'value'));
        self::assertSame(['allowed', 'denied'], array_column(PropertyLifecycleWorkflowResult::cases(), 'value'));
        self::assertSame(['transition_forbidden', 'incompatible_state', 'terminal_state', 'unknown_action', 'workflow_corrupted'], array_column(PropertyLifecycleDiagnosticCode::cases(), 'value'));
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
        yield [PropertyLifecycleWorkflow::class];
        yield [PropertyLifecycleTransition::class];
        yield [PropertyLifecycleDecision::class];
        yield [PropertyLifecycleDiagnostic::class];
    }
}
