<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use PDO;

final readonly class PostgreSqlProfessionalPublicProfileStore extends AbstractPostgreSqlProfessionalProfileStore implements ProfessionalPublicProfileStore
{
    public function __construct(PDO $connection, private ProfessionalProfilePersistenceMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $professionalId): ?ProfessionalPublicProfileState
    {
        $statement = $this->connection->prepare('SELECT professional_id::text,* FROM professional_profile.public_profiles WHERE professional_id=CAST(:id AS uuid)');
        $statement->execute(['id' => $professionalId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->mapper->profileState($row) : null;
    }

    public function save(ProfessionalPublicProfileState $candidate, int $expectedVersion): ProfessionalProfileWriteResult
    {
        return $this->transaction(function () use ($candidate, $expectedVersion): ProfessionalProfileWriteResult {
            $this->lock($candidate->professionalId);
            $intent = $this->intent('public_profile_intents', $candidate->professionalId, $candidate->intentId, $candidate->intentChecksum);
            if ($intent !== null) {
                return $intent;
            }
            $current = $this->read($candidate->professionalId);
            $currentVersion = $current === null ? 0 : $current->version;
            $currentRevision = $current === null ? 0 : $current->revision;
            if ($currentVersion !== $expectedVersion || $candidate->version !== $expectedVersion + 1 || $candidate->revision !== $currentRevision + 1) {
                return ProfessionalProfileWriteResult::VersionConflict;
            }
            $p = $this->mapper->profileParameters($candidate);
            $statement = $this->connection->prepare(
                'INSERT INTO professional_profile.public_profiles(professional_id,visibility,public_name,description,categories,languages,public_contacts,media_references,revision,version,policy_version,last_intent_id,last_intent_checksum,updated_at)
                 VALUES(CAST(:professional_id AS uuid),:visibility,:public_name,:description,CAST(:categories AS jsonb),CAST(:languages AS jsonb),CAST(:public_contacts AS jsonb),CAST(:media_references AS jsonb),:revision,:version,:policy_version,CAST(:intent_id AS uuid),:intent_checksum,CAST(:updated_at AS timestamptz))
                 ON CONFLICT(professional_id) DO UPDATE SET visibility=EXCLUDED.visibility,public_name=EXCLUDED.public_name,description=EXCLUDED.description,categories=EXCLUDED.categories,languages=EXCLUDED.languages,public_contacts=EXCLUDED.public_contacts,media_references=EXCLUDED.media_references,revision=EXCLUDED.revision,version=EXCLUDED.version,policy_version=EXCLUDED.policy_version,last_intent_id=EXCLUDED.last_intent_id,last_intent_checksum=EXCLUDED.last_intent_checksum,updated_at=EXCLUDED.updated_at',
            );
            $statement->execute($p);
            $snapshot = $this->mapper->profileSnapshot($candidate);
            $revision = $this->connection->prepare('INSERT INTO professional_profile.public_profile_revisions(professional_id,revision,version,snapshot,snapshot_checksum,recorded_at) VALUES(CAST(:id AS uuid),:revision,:version,CAST(:snapshot AS jsonb),:checksum,CAST(:at AS timestamptz))');
            $revision->execute(['id' => $candidate->professionalId, 'revision' => $candidate->revision, 'version' => $candidate->version, 'snapshot' => $snapshot, 'checksum' => hash('sha256', $snapshot), 'at' => $p['updated_at']]);
            $this->recordIntent('public_profile_intents', $candidate->professionalId, $candidate->intentId, $candidate->intentChecksum, (string) $p['updated_at']);

            return ProfessionalProfileWriteResult::Applied;
        });
    }
}
