<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppend;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransitionContextMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlAdministrativeActionContextualTransitionRepository implements AdministrativeActionContextualTransitionStore
{
    public function __construct(
        private PDO $connection,
        private AdministrativeActionLifecycleWorkflowStore $lifecycle,
        private AdministrativeActionTransitionContextMapper $mapper,
    ) {}

    public function read(AdministrativeActionId $actionId): AdministrativeActionLifecyclePersistenceReadResult
    {
        return $this->lifecycle->read($actionId);
    }

    public function append(AdministrativeActionContextualAppend $append): AdministrativeActionContextualWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $result = $this->lifecycle->append($this->mapper->mutation($append));
            $mapped = match ($result) {
                AdministrativeActionHistoricalMirrorWriteResult::Applied => $this->persistContext($append),
                AdministrativeActionHistoricalMirrorWriteResult::AlreadyApplied => $this->classifyReplay($append),
                AdministrativeActionHistoricalMirrorWriteResult::VersionConflict => AdministrativeActionContextualWriteResult::VersionConflict,
                AdministrativeActionHistoricalMirrorWriteResult::StateConflict => AdministrativeActionContextualWriteResult::StateConflict,
                AdministrativeActionHistoricalMirrorWriteResult::MirrorDivergence => $this->classifyMirrorDivergence($append),
                AdministrativeActionHistoricalMirrorWriteResult::EnrollmentDivergence => AdministrativeActionContextualWriteResult::EnrollmentDivergence,
                AdministrativeActionHistoricalMirrorWriteResult::Corrupted => AdministrativeActionContextualWriteResult::Corrupted,
                AdministrativeActionHistoricalMirrorWriteResult::PersistenceCorrupted => AdministrativeActionContextualWriteResult::PersistenceCorrupted,
            };

            if ($owner) {
                $this->connection->commit();
            }

            return $mapped;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function persistContext(AdministrativeActionContextualAppend $append): AdministrativeActionContextualWriteResult
    {
        $parameters = $this->mapper->context($append);
        $statement = $this->connection->prepare('INSERT INTO administration_audit.administrative_action_lifecycle_transition_contexts(action_id,version,contract_version,expected_version,actor_id,occurred_at,transition_action,approval_id,decision_id,historical_reason,decision_context_version,reason_evidence,recording_disposition,author_id,decision_actor_id,decision_context_checksum,context_checksum) VALUES(CAST(:action_id AS uuid),:version,:contract_version,:expected_version,:actor_id,CAST(:occurred_at AS timestamptz),:transition_action,CAST(:approval_id AS uuid),CAST(:decision_id AS uuid),:historical_reason,:decision_context_version,:reason_evidence,:recording_disposition,:author_id,:decision_actor_id,:decision_context_checksum,:context_checksum)');
        $statement->execute($parameters);

        return AdministrativeActionContextualWriteResult::Applied;
    }

    private function classifyReplay(AdministrativeActionContextualAppend $append): AdministrativeActionContextualWriteResult
    {
        $statement = $this->connection->prepare('SELECT context_checksum FROM administration_audit.administrative_action_lifecycle_transition_contexts WHERE action_id=:action_id AND version=:version');
        $statement->execute(['action_id' => $append->actionId->value, 'version' => $append->nextVersion()]);
        $checksum = $statement->fetchColumn();
        if (! is_string($checksum)) {
            return AdministrativeActionContextualWriteResult::Corrupted;
        }

        return hash_equals($checksum, $append->context->checksum()->value)
            ? AdministrativeActionContextualWriteResult::AlreadyApplied
            : AdministrativeActionContextualWriteResult::ContextDivergence;
    }

    private function classifyMirrorDivergence(AdministrativeActionContextualAppend $append): AdministrativeActionContextualWriteResult
    {
        $statement = $this->connection->prepare('SELECT previous_state,current_state,action FROM administration_audit.administrative_action_lifecycle_transitions WHERE action_id=:action_id AND version=:version AND entry_kind=\'transition\'');
        $statement->execute(['action_id' => $append->actionId->value, 'version' => $append->nextVersion()]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return AdministrativeActionContextualWriteResult::Corrupted;
        }
        if ((string) $row['previous_state'] !== $append->transition->from->value
            || (string) $row['current_state'] !== $append->transition->to->value
            || (string) $row['action'] !== $append->transition->action->value) {
            return AdministrativeActionContextualWriteResult::TransitionDivergence;
        }

        return $this->classifyReplay($append);
    }
}
