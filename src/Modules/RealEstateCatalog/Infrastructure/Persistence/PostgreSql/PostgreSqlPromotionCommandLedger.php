<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromotionCommandRecord;
use PDO;

final readonly class PostgreSqlPromotionCommandLedger implements PromotionCommandLedger
{
    public function __construct(private PDO $connection) {}

    public function find(string $commandId): ?PromotionCommandRecord
    {
        $statement = $this->connection->prepare('SELECT command_id::text, checksum, property_id::text, owner_account_id::text, authoring_version, to_char(occurred_at AT TIME ZONE \'UTC\',\'YYYY-MM-DD"T"HH24:MI:SS.US"Z"\') AS occurred_at FROM real_estate_catalog.property_promotion_commands WHERE command_id=CAST(:id AS uuid)');
        $statement->execute(['id' => $commandId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : new PromotionCommandRecord((string) $row['command_id'], (string) $row['checksum'], (string) $row['property_id'], (string) $row['owner_account_id'], (int) $row['authoring_version'], (string) $row['occurred_at']);
    }

    public function record(PromotionCommandRecord $record): void
    {
        $statement = $this->connection->prepare('INSERT INTO real_estate_catalog.property_promotion_commands(command_id,checksum,property_id,owner_account_id,authoring_version,occurred_at,result) VALUES(CAST(:command_id AS uuid),:checksum,CAST(:property_id AS uuid),CAST(:owner_account_id AS uuid),:authoring_version,CAST(:occurred_at AS timestamptz),\'applied\')');
        $statement->execute([
            'command_id' => $record->commandId,
            'checksum' => $record->checksum,
            'property_id' => $record->propertyId,
            'owner_account_id' => $record->ownerAccountId,
            'authoring_version' => $record->authoringVersion,
            'occurred_at' => $record->occurredAt,
        ]);
    }
}
