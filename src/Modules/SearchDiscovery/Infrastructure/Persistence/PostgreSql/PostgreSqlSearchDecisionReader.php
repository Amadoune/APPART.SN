<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchDecisionMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlSearchDecisionReader implements SearchDecisionReader
{
    public function __construct(private PDO $connection, private SearchDecisionMapper $mapper) {}

    public function readByListing(ListingId $listingId): SearchDecisionReadResult
    {
        $statement = $this->connection->prepare('SELECT decision_id,listing_id,version,state,payload::text AS payload,payload_checksum FROM search_discovery.public_search_decisions WHERE listing_id=:listing_id');
        $statement->execute(['listing_id' => $listingId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return SearchDecisionReadResult::missing($listingId);
        }
        try {
            return SearchDecisionReadResult::found($listingId, $this->mapper->toDecision($row));
        } catch (Throwable) {
            return SearchDecisionReadResult::corrupted($listingId);
        }
    }
}
