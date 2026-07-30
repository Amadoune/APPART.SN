<?php

namespace Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchDecisionMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlSearchDecisionWriter implements SearchDecisionWriter
{
    public function __construct(private PDO $connection, private SearchDecisionMapper $mapper) {}

    public function store(SearchDecision $decision): SearchDecisionWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $parameters = $this->mapper->parameters($decision);
            $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:listing_id,0))');
            $statement->execute(['listing_id' => $decision->listingId->value]);
            $statement = $this->connection->prepare('SELECT version,payload_checksum FROM search_discovery.public_search_decisions WHERE listing_id=:listing_id FOR UPDATE');
            $statement->execute(['listing_id' => $decision->listingId->value]);
            $existing = $statement->fetch(PDO::FETCH_ASSOC);
            if ($existing !== false) {
                $version = (int) $existing['version'];
                if ($decision->version < $version) {
                    return $this->finish(SearchDecisionWriteResult::RejectedObsolete, $owner);
                }
                if ($decision->version === $version) {
                    $result = hash_equals((string) $existing['payload_checksum'], $parameters['checksum']) ? SearchDecisionWriteResult::AlreadyApplied : SearchDecisionWriteResult::Divergent;

                    return $this->finish($result, $owner);
                }
            }
            $statement = $this->connection->prepare('INSERT INTO search_discovery.public_search_decisions(decision_id,listing_id,version,state,payload,payload_checksum) VALUES(:decision_id,:listing_id,:version,:state,CAST(:payload AS jsonb),:checksum) ON CONFLICT(listing_id) DO UPDATE SET decision_id=EXCLUDED.decision_id,version=EXCLUDED.version,state=EXCLUDED.state,payload=EXCLUDED.payload,payload_checksum=EXCLUDED.payload_checksum,updated_at=clock_timestamp() WHERE EXCLUDED.version > public_search_decisions.version');
            $statement->execute($parameters);
            if ($statement->rowCount() === 0) {
                $statement = $this->connection->prepare('SELECT version,payload_checksum FROM search_discovery.public_search_decisions WHERE listing_id=:listing_id FOR UPDATE');
                $statement->execute(['listing_id' => $decision->listingId->value]);
                $current = $statement->fetch(PDO::FETCH_ASSOC);
                if ($current !== false && $decision->version === (int) $current['version']) {
                    return $this->finish(hash_equals((string) $current['payload_checksum'], $parameters['checksum']) ? SearchDecisionWriteResult::AlreadyApplied : SearchDecisionWriteResult::Divergent, $owner);
                }

                return $this->finish(SearchDecisionWriteResult::RejectedObsolete, $owner);
            }

            return $this->finish(SearchDecisionWriteResult::Applied, $owner);
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function finish(SearchDecisionWriteResult $result, bool $owner): SearchDecisionWriteResult
    {
        if ($owner) {
            $this->connection->commit();
        }

        return $result;
    }
}
