<?php

namespace Appart\Modules\LegacyMigration\Application\Outbox;

use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryV1;

final readonly class LegacyMigrationOutboxResult
{
    public function __construct(
        public string $messageId,
        public LegacyMigrationOutboxStatus $status,
        public LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery,
        public int $retryCount,
    ) {}
}
