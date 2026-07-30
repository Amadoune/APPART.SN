<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalVerificationStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalVerificationState;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use PDO;

final readonly class PostgreSqlProfessionalVerificationStore extends AbstractPostgreSqlProfessionalProfileStore implements ProfessionalVerificationStore
{
    public function __construct(PDO $connection, private ProfessionalProfilePersistenceMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $professionalId): ?ProfessionalVerificationState
    {
        $statement = $this->connection->prepare('SELECT professional_id::text,* FROM professional_profile.verifications WHERE professional_id=CAST(:id AS uuid)');
        $statement->execute(['id' => $professionalId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->mapper->verificationState($row) : null;
    }

    public function save(ProfessionalVerificationState $candidate, int $expectedVersion): ProfessionalProfileWriteResult
    {
        return $this->transaction(function () use ($candidate, $expectedVersion): ProfessionalProfileWriteResult {
            $this->lock($candidate->professionalId);
            $intent = $this->intent('verification_intents', $candidate->professionalId, $candidate->intentId, $candidate->intentChecksum);
            if ($intent !== null) {
                return $intent;
            }
            $current = $this->read($candidate->professionalId);
            $currentVersion = $current === null ? 0 : $current->version;
            $currentDecisionSequence = $current === null ? -1 : $current->decisionSequence;
            if ($currentVersion !== $expectedVersion || $candidate->version !== $expectedVersion + 1 || $candidate->decisionSequence !== $currentDecisionSequence + 1) {
                return ProfessionalProfileWriteResult::VersionConflict;
            }
            $p = $this->mapper->verificationParameters($candidate);
            $statement = $this->connection->prepare(
                'INSERT INTO professional_profile.verifications(professional_id,disposition,evidence_references,policy_version,decision_authority_id,expires_at,decision_sequence,version,last_intent_id,last_intent_checksum,updated_at)
                 VALUES(CAST(:professional_id AS uuid),:disposition,CAST(:evidence_references AS jsonb),:policy_version,CAST(:decision_authority_id AS uuid),CAST(:expires_at AS timestamptz),:decision_sequence,:version,CAST(:intent_id AS uuid),:intent_checksum,CAST(:updated_at AS timestamptz))
                 ON CONFLICT(professional_id) DO UPDATE SET disposition=EXCLUDED.disposition,evidence_references=EXCLUDED.evidence_references,policy_version=EXCLUDED.policy_version,decision_authority_id=EXCLUDED.decision_authority_id,expires_at=EXCLUDED.expires_at,decision_sequence=EXCLUDED.decision_sequence,version=EXCLUDED.version,last_intent_id=EXCLUDED.last_intent_id,last_intent_checksum=EXCLUDED.last_intent_checksum,updated_at=EXCLUDED.updated_at',
            );
            $statement->execute($p);
            $decision = $this->connection->prepare('INSERT INTO professional_profile.verification_decisions(professional_id,decision_sequence,disposition,policy_version,decision_authority_id,evidence_references,decided_at) VALUES(CAST(:id AS uuid),:sequence,:disposition,:policy,CAST(:authority AS uuid),CAST(:evidence AS jsonb),CAST(:at AS timestamptz))');
            $decision->execute(['id' => $candidate->professionalId, 'sequence' => $candidate->decisionSequence, 'disposition' => $candidate->disposition->value, 'policy' => $candidate->policyVersion, 'authority' => $candidate->decisionAuthorityId, 'evidence' => $p['evidence_references'], 'at' => $p['updated_at']]);
            $this->recordIntent('verification_intents', $candidate->professionalId, $candidate->intentId, $candidate->intentChecksum, (string) $p['updated_at']);

            return ProfessionalProfileWriteResult::Applied;
        });
    }
}
