<?php

namespace Tests\Unit\Notifications\PublicRead;

use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotificationsPublicReadContractsTest extends TestCase
{
    #[Test]
    public function catalogs_are_closed_and_results_only_expose_their_status(): void
    {
        self::assertSame(['enabled', 'disabled', 'missing', 'corrupted', 'dependency_unavailable'], array_column(NotificationPreferenceStatusV1::cases(), 'value'));
        self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_column(NotificationTemplateStatusV1::cases(), 'value'));
        self::assertSame(['allowed', 'blocked', 'missing', 'corrupted', 'dependency_unavailable'], array_column(NotificationChannelStatusV1::cases(), 'value'));

        self::assertSame(['status'], array_keys(get_object_vars(new NotificationPreferenceResultV1(NotificationPreferenceStatusV1::Enabled))));
        self::assertSame(['status'], array_keys(get_object_vars(new NotificationTemplateResultV1(NotificationTemplateStatusV1::Available))));
        self::assertSame(['status'], array_keys(get_object_vars(new NotificationChannelResultV1(NotificationChannelStatusV1::Allowed))));
    }

    #[Test]
    public function value_objects_are_explicit_immutable_and_canonical(): void
    {
        $subject = new NotificationSubjectKey(' notification:weekly-digest ');
        $observedAt = new NotificationObservedAt(new DateTimeImmutable('2026-08-02 14:15:16.123456+02:00'));

        self::assertSame('notification:weekly-digest', $subject->value);
        self::assertSame('2026-08-02T12:15:16.123456Z', $observedAt->canonical());
    }
}
