<?php

namespace Appart\Modules\LegacyMigration\Application\Outbox;

use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryV1;

final readonly class LegacyMigrationOutboxPolicy
{
    public const MAX_RETRIES = 10;

    public function prepare(LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery): LegacyMigrationOutboxResult
    {
        return new LegacyMigrationOutboxResult($this->messageId($delivery), LegacyMigrationOutboxStatus::Applied, $delivery, 0);
    }

    public function messageId(LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery): string
    {
        return hash('sha256', $this->canonical($delivery));
    }

    public function checksum(LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery): string
    {
        return hash('sha256', "legacy-migration-outbox-v1\n".$this->canonical($delivery));
    }

    public function canonical(LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery): string
    {
        return json_encode([
            'type' => $delivery->type->value,
            'status' => $delivery->payload->status->value,
            'observedAt' => $delivery->payload->observedAt,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
