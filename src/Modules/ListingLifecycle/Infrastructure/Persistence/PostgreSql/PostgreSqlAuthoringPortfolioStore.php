<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPortfolioItem;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\AuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\AuthoringPortfolioMapper;
use PDO;
use PDOException;

final readonly class PostgreSqlAuthoringPortfolioStore implements AuthoringPortfolioStore
{
    public function __construct(private PDO $connection, private AuthoringPortfolioMapper $mapper) {}

    public function listFor(string $accountId): array
    {
        $statement = $this->connection->prepare('SELECT * FROM listing_authoring.portfolio_items WHERE account_id=:id ORDER BY source_checkpoint,listing_id');
        $statement->execute(['id' => $accountId]);

        return array_map($this->mapper->toItem(...), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function project(AuthoringPortfolioItem $item): AuthoringPersistenceWriteResult
    {
        $statement = $this->connection->prepare('INSERT INTO listing_authoring.portfolio_items(account_id,listing_id,property_id,relation,draft_version,ownership_version,completeness_code,source_checkpoint) VALUES(:account_id,:listing_id,:property_id,:relation,:draft_version,:ownership_version,:completeness_code,:source_checkpoint) ON CONFLICT(account_id,listing_id) DO UPDATE SET property_id=EXCLUDED.property_id,relation=EXCLUDED.relation,draft_version=EXCLUDED.draft_version,ownership_version=EXCLUDED.ownership_version,completeness_code=EXCLUDED.completeness_code,source_checkpoint=EXCLUDED.source_checkpoint,projected_at=CURRENT_TIMESTAMP WHERE listing_authoring.portfolio_items.source_checkpoint < EXCLUDED.source_checkpoint');
        try {
            $statement->execute(['account_id' => $item->accountId, 'listing_id' => $item->listingId, 'property_id' => $item->propertyId, 'relation' => $item->relation, 'draft_version' => $item->draftVersion, 'ownership_version' => $item->ownershipVersion, 'completeness_code' => $item->completenessCode, 'source_checkpoint' => $item->sourceCheckpoint]);

            return $statement->rowCount() === 1
                ? AuthoringPersistenceWriteResult::Applied
                : AuthoringPersistenceWriteResult::AlreadyApplied;
        } catch (PDOException) {
            return AuthoringPersistenceWriteResult::Rejected;
        }
    }
}
