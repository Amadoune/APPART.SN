<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecycleStoredState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use RuntimeException;

final readonly class AdministrativeActionLifecycleWorkflowMapper
{
    /** @return array<string, int|string|null> */
    public function enrollment(AdministrativeActionLifecycleEnrollmentCheckpoint $checkpoint): array
    {
        return [
            'action_id' => $checkpoint->actionId->value,
            'version' => $checkpoint->historicalVersion,
            'entry_kind' => 'enrollment',
            'previous_state' => null,
            'current_state' => $checkpoint->state->value,
            'action' => null,
            'entry_checksum' => $this->entryChecksum(
                $checkpoint->actionId,
                $checkpoint->historicalVersion,
                null,
                $checkpoint->state,
                null,
            ),
            'source_checksum' => $checkpoint->sourceChecksum->value,
            'mirror_checksum' => null,
        ];
    }

    /** @return array<string, int|string|null> */
    public function transition(AdministrativeActionHistoricalMirrorMutation $mutation): array
    {
        $version = $mutation->expectedHistoricalVersion + 1;

        return [
            'action_id' => $mutation->actionId->value,
            'version' => $version,
            'entry_kind' => 'transition',
            'previous_state' => $mutation->transition->from->value,
            'current_state' => $mutation->transition->to->value,
            'action' => $mutation->transition->action->value,
            'entry_checksum' => $this->entryChecksum(
                $mutation->actionId,
                $version,
                $mutation->transition->from,
                $mutation->transition->to,
                $mutation->transition->action,
            ),
            'source_checksum' => null,
            'mirror_checksum' => $mutation->checksum()->value,
        ];
    }

    /** @param array<string, mixed> $row */
    public function storedState(array $row): AdministrativeActionLifecycleStoredState
    {
        $actionId = AdministrativeActionId::fromString((string) $row['action_id']);
        $version = filter_var($row['version'], FILTER_VALIDATE_INT);
        $state = AdministrativeActionLifecycleState::tryFrom((string) $row['current_state']);
        if ($version === false || $version < 0 || $state === null) {
            throw new RuntimeException('Corrupted Administrative Action Lifecycle row.');
        }

        $previousState = $row['previous_state'] === null
            ? null
            : AdministrativeActionLifecycleState::tryFrom((string) $row['previous_state']);
        $action = $row['action'] === null
            ? null
            : AdministrativeActionLifecycleAction::tryFrom((string) $row['action']);
        if (($row['previous_state'] !== null && $previousState === null) || ($row['action'] !== null && $action === null)) {
            throw new RuntimeException('Corrupted Administrative Action Lifecycle transition.');
        }

        $expected = $this->entryChecksum($actionId, $version, $previousState, $state, $action);
        if (! hash_equals($expected, (string) $row['entry_checksum'])) {
            throw new RuntimeException('Corrupted Administrative Action Lifecycle checksum.');
        }

        return new AdministrativeActionLifecycleStoredState($actionId, $version, $state, $expected);
    }

    private function entryChecksum(
        AdministrativeActionId $actionId,
        int $version,
        ?AdministrativeActionLifecycleState $previousState,
        AdministrativeActionLifecycleState $currentState,
        ?AdministrativeActionLifecycleAction $action,
    ): string {
        return hash('sha256', implode("\n", [
            $actionId->value,
            (string) $version,
            $previousState === null ? '' : $previousState->value,
            $currentState->value,
            $action === null ? '' : $action->value,
        ]));
    }
}
