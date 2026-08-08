<?php

namespace Tests\Unit\Notifications\Outbox;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryPayload;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryStatus;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryV1;
use Appart\Modules\Notifications\Application\Event\NotificationEventType;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxPolicy;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotificationOutboxPolicyTest extends TestCase
{
    #[DataProvider('deliveries')]
    public function test_identity_and_checksum_are_canonical_and_deterministic(NotificationEventType $type, NotificationDeliveryStatus $status): void
    {
        $delivery = new NotificationDeliveryV1(new NotificationDeliveryPayload($type, $status, '2026-08-02T12:00:00.123456Z'));
        $policy = new NotificationOutboxPolicy;
        self::assertSame($policy->messageId($delivery), $policy->prepare($delivery)->messageId);
        self::assertSame($policy->messageId($delivery), $policy->messageId($delivery));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $policy->checksum($delivery));
        self::assertSame(NotificationOutboxStatus::Applied, $policy->prepare($delivery)->status);
    }

    /** @return iterable<array{NotificationEventType, NotificationDeliveryStatus}> */
    public static function deliveries(): iterable
    {
        yield [NotificationEventType::PreferenceObserved, NotificationDeliveryStatus::Enabled];
        yield [NotificationEventType::PreferenceObserved, NotificationDeliveryStatus::Disabled];
        yield [NotificationEventType::TemplateObserved, NotificationDeliveryStatus::Available];
        yield [NotificationEventType::ChannelObserved, NotificationDeliveryStatus::Allowed];
        yield [NotificationEventType::ChannelObserved, NotificationDeliveryStatus::Blocked];
        foreach ([NotificationEventType::PreferenceObserved, NotificationEventType::TemplateObserved, NotificationEventType::ChannelObserved] as $type) {
            foreach ([NotificationDeliveryStatus::Missing, NotificationDeliveryStatus::Corrupted, NotificationDeliveryStatus::DependencyUnavailable] as $status) {
                yield [$type, $status];
            }
        }
    }
}
