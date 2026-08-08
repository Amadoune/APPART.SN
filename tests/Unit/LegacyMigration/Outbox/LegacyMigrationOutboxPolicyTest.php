<?php

namespace Tests\Unit\LegacyMigration\Outbox;

use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventType;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxPolicy;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyMigrationOutboxPolicyTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_identity_json_and_checksum_are_canonical(LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1 $delivery): void
    {
        $policy = new LegacyMigrationOutboxPolicy;
        $entry = $policy->prepare($delivery);
        self::assertSame($policy->messageId($delivery), $entry->messageId);
        self::assertSame($policy->messageId($delivery), $policy->messageId($delivery));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $policy->checksum($delivery));
        self::assertSame(LegacyMigrationOutboxStatus::Applied, $entry->status);
        self::assertSame(0, $entry->retryCount);
        self::assertSame(['type', 'status', 'observedAt'], array_keys(json_decode($policy->canonical($delivery), true, 512, JSON_THROW_ON_ERROR)));
    }

    /** @return iterable<string, array{LegacyMigrationInventoryDeliveryV1|LegacyMigrationWaveDeliveryV1|LegacyMigrationReconciliationDeliveryV1|LegacyMigrationQuarantineDeliveryV1|LegacyMigrationCutoverDeliveryV1}> */
    public static function deliveries(): iterable
    {
        foreach (LegacyMigrationInventoryDeliveryStatus::cases() as $status) {
            yield 'inventory '.$status->value => [new LegacyMigrationInventoryDeliveryV1(LegacyMigrationInventoryEventType::Observed, new LegacyMigrationInventoryDeliveryPayload($status, self::observedAt()))];
        }
        foreach (LegacyMigrationWaveDeliveryStatus::cases() as $status) {
            yield 'wave '.$status->value => [new LegacyMigrationWaveDeliveryV1(LegacyMigrationWaveEventType::Observed, new LegacyMigrationWaveDeliveryPayload($status, self::observedAt()))];
        }
        foreach (LegacyMigrationReconciliationDeliveryStatus::cases() as $status) {
            yield 'reconciliation '.$status->value => [new LegacyMigrationReconciliationDeliveryV1(LegacyMigrationReconciliationEventType::Observed, new LegacyMigrationReconciliationDeliveryPayload($status, self::observedAt()))];
        }
        foreach (LegacyMigrationQuarantineDeliveryStatus::cases() as $status) {
            yield 'quarantine '.$status->value => [new LegacyMigrationQuarantineDeliveryV1(LegacyMigrationQuarantineEventType::Observed, new LegacyMigrationQuarantineDeliveryPayload($status, self::observedAt()))];
        }
        foreach (LegacyMigrationCutoverDeliveryStatus::cases() as $status) {
            yield 'cutover '.$status->value => [new LegacyMigrationCutoverDeliveryV1(LegacyMigrationCutoverEventType::Observed, new LegacyMigrationCutoverDeliveryPayload($status, self::observedAt()))];
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-04T12:00:00.123456Z';
    }
}
