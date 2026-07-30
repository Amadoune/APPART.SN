<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextChecksum;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppendInspection;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionResult;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final readonly class PostgreSqlProfessionalStatusContextualReplayInspector implements ProfessionalStatusContextualReplayInspector
{
    public function __construct(private PDO $connection, private ProfessionalStatusWorkflowMapper $workflowMapper, private ProfessionalStatusContextMapper $contextMapper) {}

    public function inspectLatest(ProfessionalStatusId $professionalId): ProfessionalStatusContextualInspectionResult
    {
        $statement = $this->connection->prepare('SELECT t.professional_id::text,t.version,t.previous_state,t.current_state,t.action,t.transition_checksum,c.actor_id::text,c.occurred_at,c.context_checksum FROM professionals.professional_status_transitions t JOIN professionals.professional_status_transition_contexts c USING(professional_id,version) WHERE t.professional_id=:id ORDER BY t.version DESC LIMIT 1');
        $statement->execute(['id' => $professionalId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return ProfessionalStatusContextualInspectionResult::missing($professionalId);
        }

        try {
            $occurredAt = (new DateTimeImmutable((string) $row['occurred_at']))->setTimezone(new DateTimeZone('UTC'));
            $canonical = $row;
            $canonical['occurred_at'] = $occurredAt->format('Y-m-d\TH:i:s.uP');
            if (! hash_equals((string) $row['transition_checksum'], $this->workflowMapper->checksum($row))
                || ! hash_equals((string) $row['context_checksum'], $this->contextMapper->checksum($canonical))) {
                return ProfessionalStatusContextualInspectionResult::corrupted($professionalId);
            }

            return ProfessionalStatusContextualInspectionResult::found(new ProfessionalStatusContextualAppendInspection(
                $professionalId,
                (int) $row['version'],
                new ProfessionalStatusTransition(ProfessionalStatusState::from((string) $row['previous_state']), ProfessionalStatusState::from((string) $row['current_state']), ProfessionalStatusAction::from((string) $row['action'])),
                ProfessionalStatusActorId::fromString((string) $row['actor_id']),
                ProfessionalStatusOccurredAt::fromExplicitUtc($occurredAt),
                ProfessionalStatusContextChecksum::fromString((string) $row['context_checksum']),
            ));
        } catch (Throwable) {
            return ProfessionalStatusContextualInspectionResult::corrupted($professionalId);
        }
    }
}
