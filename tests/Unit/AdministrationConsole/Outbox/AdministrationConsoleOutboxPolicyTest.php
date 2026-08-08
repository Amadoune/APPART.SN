<?php

namespace Tests\Unit\AdministrationConsole\Outbox;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventType;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxPolicy;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleOutboxPolicyTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_identity_and_checksum_are_canonical_and_deterministic(object $delivery): void
    {
        $policy = new AdministrationConsoleOutboxPolicy;
        $entry = $policy->prepare($delivery);

        self::assertSame($policy->messageId($delivery), $entry->messageId);
        self::assertSame($policy->messageId($delivery), $policy->messageId($delivery));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $policy->checksum($delivery));
        self::assertSame(AdministrationConsoleOutboxStatus::Applied, $entry->status);
        self::assertSame(0, $entry->retryCount);
        self::assertSame(['type', 'status', 'observedAt'], array_keys(json_decode($policy->canonical($delivery), true, 512, JSON_THROW_ON_ERROR)));
    }

    /** @return iterable<string, array{AdministrationOperatorDeliveryV1|AdministrationQueueDeliveryV1|AdministrationAuditDeliveryV1}> */
    public static function deliveries(): iterable
    {
        foreach (AdministrationOperatorDeliveryStatus::cases() as $status) {
            yield 'operator '.$status->value => [new AdministrationOperatorDeliveryV1(AdministrationOperatorEventType::Observed, new AdministrationOperatorDeliveryPayload($status, self::observedAt()))];
        }
        foreach (AdministrationQueueDeliveryStatus::cases() as $status) {
            yield 'queue '.$status->value => [new AdministrationQueueDeliveryV1(AdministrationQueueEventType::Observed, new AdministrationQueueDeliveryPayload($status, self::observedAt()))];
        }
        foreach (AdministrationAuditDeliveryStatus::cases() as $status) {
            yield 'audit '.$status->value => [new AdministrationAuditDeliveryV1(AdministrationAuditEventType::Observed, new AdministrationAuditDeliveryPayload($status, self::observedAt()))];
        }
    }

    private static function observedAt(): string
    {
        return '2026-08-03T12:00:00.123456Z';
    }
}
