<?php

namespace Tests\Unit\AdministrativeActionLifecycle;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleDiagnostic;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflowResult;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class AdministrativeActionLifecycleWorkflowTest extends TestCase
{
    #[DataProvider('decisions')]
    public function test_the_complete_state_action_context_matrix_is_deterministic(
        AdministrativeActionLifecycleState $state,
        AdministrativeActionLifecycleAction $action,
        AdministrativeActionDecisionContext $context,
        AdministrativeActionLifecycleWorkflowResult $result,
        ?AdministrativeActionLifecycleState $target,
        ?AdministrativeActionLifecycleDiagnostic $diagnostic,
    ): void {
        $workflow = new AdministrativeActionLifecycleWorkflow;
        $first = $workflow->decide($state, $action, $context);
        $second = $workflow->decide($state, $action, $context);

        self::assertEquals($first, $second);
        self::assertSame($result, $first->result);
        self::assertSame($target, $first->transition?->to);
        self::assertSame($diagnostic, $first->diagnostic);

        if ($result === AdministrativeActionLifecycleWorkflowResult::Allowed) {
            self::assertInstanceOf(AdministrativeActionLifecycleTransition::class, $first->transition);
            self::assertSame($state, $first->transition->from);
            self::assertSame($action, $first->transition->action);
            self::assertNull($first->diagnostic);
        } else {
            self::assertNull($first->transition);
            self::assertInstanceOf(AdministrativeActionLifecycleDiagnostic::class, $first->diagnostic);
        }
    }

    public function test_contract_sets_are_closed(): void
    {
        self::assertCount(5, AdministrativeActionLifecycleState::cases());
        self::assertCount(4, AdministrativeActionLifecycleAction::cases());
        self::assertCount(5, AdministrativeActionLifecycleDiagnostic::cases());
        self::assertSame(['allowed', 'denied'], array_column(AdministrativeActionLifecycleWorkflowResult::cases(), 'value'));
    }

    public function test_creation_is_not_an_executable_workflow_action(): void
    {
        self::assertNotContains('create', array_column(AdministrativeActionLifecycleAction::cases(), 'value'));
        self::assertFalse((new ReflectionClass(AdministrativeActionLifecycleWorkflow::class))->hasMethod('initialState'));
    }

    public function test_the_workflow_signature_consumes_only_the_certified_context(): void
    {
        $parameters = (new ReflectionMethod(AdministrativeActionLifecycleWorkflow::class, 'decide'))->getParameters();
        self::assertCount(3, $parameters);
        self::assertSame(AdministrativeActionLifecycleState::class, (string) $parameters[0]->getType());
        self::assertSame(AdministrativeActionLifecycleAction::class, (string) $parameters[1]->getType());
        self::assertSame(AdministrativeActionDecisionContext::class, (string) $parameters[2]->getType());
    }

    /** @return iterable<string, array{AdministrativeActionLifecycleState, AdministrativeActionLifecycleAction, AdministrativeActionDecisionContext, AdministrativeActionLifecycleWorkflowResult, ?AdministrativeActionLifecycleState, ?AdministrativeActionLifecycleDiagnostic}> */
    public static function decisions(): iterable
    {
        foreach (self::contexts() as $contextName => $context) {
            foreach (AdministrativeActionLifecycleState::cases() as $state) {
                foreach (AdministrativeActionLifecycleAction::cases() as $action) {
                    [$result, $target, $diagnostic] = self::expected($state, $action, $context);
                    yield $contextName.' '.$state->value.' '.$action->value => [
                        $state,
                        $action,
                        $context,
                        $result,
                        $target,
                        $diagnostic,
                    ];
                }
            }
        }
    }

    /** @return array<string, AdministrativeActionDecisionContext> */
    private static function contexts(): array
    {
        $author = ActorId::fromString('author-001');
        $decider = ActorId::fromString('decider-001');

        return [
            'direct present' => new AdministrativeActionDecisionContext(
                AdministrativeActionDecisionContextVersion::V1,
                AdministrativeActionReasonEvidence::Present,
                AdministrativeActionDecisionAuthority::directRecording($author, $author),
            ),
            'direct missing' => new AdministrativeActionDecisionContext(
                AdministrativeActionDecisionContextVersion::V1,
                AdministrativeActionReasonEvidence::Missing,
                AdministrativeActionDecisionAuthority::directRecording($author, $author),
            ),
            'independent present' => new AdministrativeActionDecisionContext(
                AdministrativeActionDecisionContextVersion::V1,
                AdministrativeActionReasonEvidence::Present,
                AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decider),
            ),
            'independent missing' => new AdministrativeActionDecisionContext(
                AdministrativeActionDecisionContextVersion::V1,
                AdministrativeActionReasonEvidence::Missing,
                AdministrativeActionDecisionAuthority::independentApprovalRequired($author, $decider),
            ),
        ];
    }

    /** @return array{AdministrativeActionLifecycleWorkflowResult, ?AdministrativeActionLifecycleState, ?AdministrativeActionLifecycleDiagnostic} */
    private static function expected(
        AdministrativeActionLifecycleState $state,
        AdministrativeActionLifecycleAction $action,
        AdministrativeActionDecisionContext $context,
    ): array {
        if ($action === AdministrativeActionLifecycleAction::Unknown) {
            return self::denied(AdministrativeActionLifecycleDiagnostic::UnknownAction);
        }

        if (in_array($state, [
            AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionLifecycleState::Approved,
            AdministrativeActionLifecycleState::Rejected,
        ], true)) {
            return self::denied(AdministrativeActionLifecycleDiagnostic::TerminalState);
        }

        if ($state === AdministrativeActionLifecycleState::Draft) {
            if ($action !== AdministrativeActionLifecycleAction::Record) {
                return self::denied(AdministrativeActionLifecycleDiagnostic::IncompatibleState);
            }
            if ($context->reasonEvidence === AdministrativeActionReasonEvidence::Missing) {
                return self::denied(AdministrativeActionLifecycleDiagnostic::MissingReason);
            }

            return [
                AdministrativeActionLifecycleWorkflowResult::Allowed,
                $context->authority->disposition->value === 'direct_recording'
                    ? AdministrativeActionLifecycleState::Recorded
                    : AdministrativeActionLifecycleState::PendingApproval,
                null,
            ];
        }

        if ($action === AdministrativeActionLifecycleAction::Record) {
            return self::denied(AdministrativeActionLifecycleDiagnostic::IncompatibleState);
        }
        if ($context->reasonEvidence === AdministrativeActionReasonEvidence::Missing) {
            return self::denied(AdministrativeActionLifecycleDiagnostic::MissingReason);
        }
        if ($context->authority->disposition->value === 'direct_recording') {
            return self::denied(AdministrativeActionLifecycleDiagnostic::ApprovalNotRequired);
        }

        return [
            AdministrativeActionLifecycleWorkflowResult::Allowed,
            $action === AdministrativeActionLifecycleAction::Approve
                ? AdministrativeActionLifecycleState::Approved
                : AdministrativeActionLifecycleState::Rejected,
            null,
        ];
    }

    /** @return array{AdministrativeActionLifecycleWorkflowResult, null, AdministrativeActionLifecycleDiagnostic} */
    private static function denied(AdministrativeActionLifecycleDiagnostic $diagnostic): array
    {
        return [AdministrativeActionLifecycleWorkflowResult::Denied, null, $diagnostic];
    }
}
