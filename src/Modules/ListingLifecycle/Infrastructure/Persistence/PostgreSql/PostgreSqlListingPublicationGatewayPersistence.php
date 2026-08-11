<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandLedgerV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationGatewayTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandLedgerRecord;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Closure;
use DateTimeImmutable;
use PDO;
use Throwable;

final readonly class PostgreSqlListingPublicationGatewayPersistence implements ListingPublicationCommandLedgerV1, ListingPublicationGatewayTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'listing_publication_gateway';
        $owner ? $this->connection->beginTransaction() : $this->connection->exec("SAVEPOINT {$savepoint}");
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $error) {
            $owner ? $this->connection->rollBack() : $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            throw $error;
        }
    }

    public function find(string $commandId): ?ListingPublicationCommandLedgerRecord
    {
        $statement = $this->connection->prepare("SELECT command_id::text,command_checksum,status,workflow_version,aggregate_version FROM listing_lifecycle.publication_command_gateway_ledger WHERE command_id=CAST(:command_id AS uuid) AND status<>'reserved'");
        $statement->execute(['command_id' => $commandId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return new ListingPublicationCommandLedgerRecord(
            (string) $row['command_id'],
            (string) $row['command_checksum'],
            ListingPublicationCommandStatus::from((string) $row['status']),
            isset($row['workflow_version']) ? (int) $row['workflow_version'] : null,
            isset($row['aggregate_version']) ? (int) $row['aggregate_version'] : null,
        );
    }

    public function reserve(string $commandId, string $listingId, string $operation, string $checksum, DateTimeImmutable $occurredAt): bool
    {
        $lock = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:command_id,0))');
        $lock->execute(['command_id' => $commandId]);
        $statement = $this->connection->prepare("INSERT INTO listing_lifecycle.publication_command_gateway_ledger(command_id,listing_id,operation,command_checksum,status,occurred_at) VALUES(CAST(:command_id AS uuid),CAST(:listing_id AS uuid),:operation,:checksum,'reserved',:occurred_at) ON CONFLICT (command_id) DO NOTHING");
        $statement->execute([
            'command_id' => $commandId,
            'listing_id' => $listingId,
            'operation' => $operation,
            'checksum' => $checksum,
            'occurred_at' => $occurredAt->format('Y-m-d H:i:s.uP'),
        ]);

        return $statement->rowCount() === 1;
    }

    public function complete(string $commandId, string $checksum, ListingPublicationCommandStatus $status, ?int $workflowVersion, ?int $aggregateVersion): bool
    {
        $statement = $this->connection->prepare("UPDATE listing_lifecycle.publication_command_gateway_ledger SET status=:status,workflow_version=:workflow_version,aggregate_version=:aggregate_version,completed_at=clock_timestamp() WHERE command_id=CAST(:command_id AS uuid) AND command_checksum=:checksum AND status='reserved'");
        $statement->execute([
            'status' => $status->value,
            'workflow_version' => $workflowVersion,
            'aggregate_version' => $aggregateVersion,
            'command_id' => $commandId,
            'checksum' => $checksum,
        ]);

        return $statement->rowCount() === 1;
    }
}
