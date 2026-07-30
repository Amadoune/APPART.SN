<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicPortfolioState;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use PDO;

final readonly class PostgreSqlProfessionalPublicPortfolioStore extends AbstractPostgreSqlProfessionalProfileStore implements ProfessionalPublicPortfolioStore
{
    public function __construct(PDO $connection, private ProfessionalProfilePersistenceMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $professionalId): ?ProfessionalPublicPortfolioState
    {
        $statement = $this->connection->prepare('SELECT professional_id::text,* FROM professional_profile.public_portfolios WHERE professional_id=CAST(:id AS uuid)');
        $statement->execute(['id' => $professionalId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->mapper->portfolioState($row) : null;
    }

    public function save(ProfessionalPublicPortfolioState $candidate, int $expectedVersion): ProfessionalProfileWriteResult
    {
        return $this->transaction(function () use ($candidate, $expectedVersion): ProfessionalProfileWriteResult {
            $this->lock($candidate->professionalId);
            $intent = $this->intent('public_portfolio_intents', $candidate->professionalId, $candidate->intentId, $candidate->intentChecksum);
            if ($intent !== null) {
                return $intent;
            }
            $current = $this->read($candidate->professionalId);
            $currentVersion = $current === null ? 0 : $current->version;
            $currentCheckpoint = $current === null ? 0 : $current->checkpoint;
            if ($currentVersion !== $expectedVersion || $candidate->version !== $expectedVersion + 1) {
                return ProfessionalProfileWriteResult::VersionConflict;
            }
            if ($candidate->checkpoint < $currentCheckpoint) {
                return ProfessionalProfileWriteResult::CheckpointRegression;
            }
            $p = $this->mapper->portfolioParameters($candidate);
            $statement = $this->connection->prepare(
                'INSERT INTO professional_profile.public_portfolios(professional_id,listing_ids,checkpoint,version,last_intent_id,last_intent_checksum,updated_at)
                 VALUES(CAST(:professional_id AS uuid),CAST(:listing_ids AS jsonb),:checkpoint,:version,CAST(:intent_id AS uuid),:intent_checksum,CAST(:updated_at AS timestamptz))
                 ON CONFLICT(professional_id) DO UPDATE SET listing_ids=EXCLUDED.listing_ids,checkpoint=EXCLUDED.checkpoint,version=EXCLUDED.version,last_intent_id=EXCLUDED.last_intent_id,last_intent_checksum=EXCLUDED.last_intent_checksum,updated_at=EXCLUDED.updated_at',
            );
            $statement->execute($p);
            $this->recordIntent('public_portfolio_intents', $candidate->professionalId, $candidate->intentId, $candidate->intentChecksum, (string) $p['updated_at']);

            return ProfessionalProfileWriteResult::Applied;
        });
    }
}
