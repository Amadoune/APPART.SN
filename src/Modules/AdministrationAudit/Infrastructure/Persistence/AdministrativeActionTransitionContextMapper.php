<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppend;

final readonly class AdministrativeActionTransitionContextMapper
{
    public function mutation(AdministrativeActionContextualAppend $append): AdministrativeActionHistoricalMirrorMutation
    {
        $context = $append->context;

        return match ($append->transition->action) {
            AdministrativeActionLifecycleAction::Record => AdministrativeActionHistoricalMirrorMutation::record(
                $append->actionId,
                $context->expectedVersion->value,
                $append->transition,
                $context->actor,
                $context->historicalReason,
                $context->occurredAt,
            ),
            AdministrativeActionLifecycleAction::Approve => AdministrativeActionHistoricalMirrorMutation::approve(
                $append->actionId,
                $context->expectedVersion->value,
                $append->transition,
                $context->actor,
                $context->historicalReason,
                $context->occurredAt,
                $context->decisionIdentities->approvalId ?? throw new \InvalidArgumentException('Approve requires an ApprovalId.'),
                $context->decisionIdentities->decisionId ?? throw new \InvalidArgumentException('Approve requires a DecisionId.'),
            ),
            AdministrativeActionLifecycleAction::Reject => AdministrativeActionHistoricalMirrorMutation::reject(
                $append->actionId,
                $context->expectedVersion->value,
                $append->transition,
                $context->actor,
                $context->historicalReason,
                $context->occurredAt,
                $context->decisionIdentities->decisionId ?? throw new \InvalidArgumentException('Reject requires a DecisionId.'),
            ),
            AdministrativeActionLifecycleAction::Unknown => throw new \InvalidArgumentException('Unknown actions cannot be persisted contextually.'),
        };
    }

    /** @return array<string, int|string|null> */
    public function context(AdministrativeActionContextualAppend $append): array
    {
        $context = $append->context;

        return [
            'action_id' => $append->actionId->value,
            'version' => $append->nextVersion(),
            'contract_version' => $context->contractVersion->value,
            'expected_version' => $context->expectedVersion->value,
            'actor_id' => $context->actor->value,
            'occurred_at' => $context->occurredAt->canonical(),
            'transition_action' => $context->decisionIdentities->action->value,
            'approval_id' => $context->decisionIdentities->approvalId === null ? null : $context->decisionIdentities->approvalId->value,
            'decision_id' => $context->decisionIdentities->decisionId === null ? null : $context->decisionIdentities->decisionId->value,
            'historical_reason' => $context->historicalReason->value,
            'decision_context_version' => $context->decisionContext->contractVersion->value,
            'reason_evidence' => $context->decisionContext->reasonEvidence->value,
            'recording_disposition' => $context->decisionContext->authority->disposition->value,
            'author_id' => $context->decisionContext->authority->author->value,
            'decision_actor_id' => $context->decisionContext->authority->decisionActor->value,
            'decision_context_checksum' => $context->decisionContext->checksum()->value,
            'context_checksum' => $context->checksum()->value,
        ];
    }
}
