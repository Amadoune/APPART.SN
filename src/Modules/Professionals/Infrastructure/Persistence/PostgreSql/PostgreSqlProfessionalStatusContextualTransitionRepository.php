<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppend;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualWriteResult;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlProfessionalStatusContextualTransitionRepository implements ProfessionalStatusContextualTransitionStore
{
    public function __construct(
        private PDO $connection,
        private ProfessionalStatusWorkflowStore $historical,
        private ProfessionalStatusWorkflowMapper $workflowMapper,
        private ProfessionalStatusContextMapper $contextMapper,
    ) {}

    public function read(ProfessionalStatusId $professionalId): ProfessionalStatusPersistenceReadResult
    {
        return $this->historical->read($professionalId);
    }

    public function append(ProfessionalStatusContextualAppend $append): ProfessionalStatusContextualWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $this->lock($append->professionalId);
            $current = $this->current($append->professionalId);
            $transition = $this->workflowMapper->transition($append->professionalId, $append->transition, $append->nextVersion());
            $context = $this->contextMapper->map($append);

            if ($current === false) {
                return $this->finish($owner, ProfessionalStatusContextualWriteResult::VersionConflict);
            }
            if ((int) $current['version'] === $append->nextVersion()) {
                if (! hash_equals((string) $current['transition_checksum'], $transition['checksum'])) {
                    return $this->finish($owner, ProfessionalStatusContextualWriteResult::StateConflict);
                }
                $stored = $this->context($append->professionalId, $append->nextVersion());
                if ($stored === false) {
                    return $this->finish($owner, ProfessionalStatusContextualWriteResult::Corrupted);
                }

                return $this->finish($owner, hash_equals((string) $stored['context_checksum'], $context['context_checksum'])
                    ? ProfessionalStatusContextualWriteResult::AlreadyApplied
                    : ProfessionalStatusContextualWriteResult::ContextDivergence);
            }
            if ((int) $current['version'] !== $append->context->expectedVersion->value) {
                return $this->finish($owner, ProfessionalStatusContextualWriteResult::VersionConflict);
            }
            if ((string) $current['current_state'] !== $append->transition->from->value) {
                return $this->finish($owner, ProfessionalStatusContextualWriteResult::StateConflict);
            }

            try {
                $this->insertTransition($transition);
                $this->insertContext($context);
            } catch (PDOException $error) {
                if ($error->getCode() === '23514') {
                    return $this->finish($owner, ProfessionalStatusContextualWriteResult::TransitionRejected);
                }
                throw $error;
            }

            return $this->finish($owner, ProfessionalStatusContextualWriteResult::Applied);
        } catch (Throwable $error) {
            if ($owner && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function finish(bool $owner, ProfessionalStatusContextualWriteResult $result): ProfessionalStatusContextualWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }

    private function lock(ProfessionalStatusId $id): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:id,0))');
        $statement->execute(['id' => $id->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(ProfessionalStatusId $id): array|false
    {
        $statement = $this->connection->prepare('SELECT version,current_state,transition_checksum FROM professionals.professional_status_transitions WHERE professional_id=:id ORDER BY version DESC LIMIT 1 FOR UPDATE');
        $statement->execute(['id' => $id->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|false */
    private function context(ProfessionalStatusId $id, int $version): array|false
    {
        $statement = $this->connection->prepare('SELECT context_checksum FROM professionals.professional_status_transition_contexts WHERE professional_id=:id AND version=:version');
        $statement->execute(['id' => $id->value, 'version' => $version]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /** @param array<string, mixed> $parameters */
    private function insertTransition(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO professionals.professional_status_transitions(professional_id,version,previous_state,current_state,action,transition_checksum) VALUES(CAST(:professional_id AS uuid),:version,:previous_state,:current_state,:action,:checksum)');
        $statement->execute($parameters);
    }

    /** @param array<string, mixed> $parameters */
    private function insertContext(array $parameters): void
    {
        $statement = $this->connection->prepare('INSERT INTO professionals.professional_status_transition_contexts(professional_id,version,actor_id,occurred_at,context_checksum) VALUES(CAST(:professional_id AS uuid),:version,CAST(:actor_id AS uuid),CAST(:occurred_at AS timestamptz),:context_checksum)');
        $statement->execute($parameters);
    }
}
