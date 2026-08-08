<?php

namespace Tests\Unit\Notifications\Event;

use Appart\Modules\Notifications\Application\Event\NotificationEventFactory;
use Appart\Modules\Notifications\Application\Event\NotificationEventStatus;
use Appart\Modules\Notifications\Application\Event\NotificationEventType;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationChannelReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationPreferenceReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationTemplateReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotificationEventFactoryTest extends TestCase
{
    #[DataProvider('events')]
    public function test_every_public_result_produces_exactly_one_event(string $kind, object $result, NotificationEventType $type, NotificationEventStatus $status): void
    {
        $preference = $this->createMock(NotificationPreferenceReaderV1::class);
        $template = $this->createMock(NotificationTemplateReaderV1::class);
        $channel = $this->createMock(NotificationChannelReaderV1::class);
        match ($kind) {
            'preference' => $preference->method('read')->willReturn($result),
            'template' => $template->method('read')->willReturn($result),
            'channel' => $channel->method('read')->willReturn($result),
            default => throw new \LogicException('Unknown notification event kind.'),
        };
        $event = (new NotificationEventFactory($preference, $template, $channel))->{$kind}(new NotificationSubjectKey('subject:42'),new NotificationObservedAt(new DateTimeImmutable('2026-08-02T12:00:00.123456Z')));
        self::assertSame($type, $event->type);
        self::assertSame(['status' => $status->value, 'observedAt' => '2026-08-02T12:00:00.123456Z'], $event->payload->canonical());
    }

    /** @return iterable<string, array{'preference'|'template'|'channel', NotificationPreferenceResultV1|NotificationTemplateResultV1|NotificationChannelResultV1, NotificationEventType, NotificationEventStatus}> */
    public static function events(): iterable
    {
        foreach (NotificationPreferenceStatusV1::cases() as $status) {
            yield 'preference '.$status->value => ['preference', new NotificationPreferenceResultV1($status), NotificationEventType::PreferenceObserved, NotificationEventStatus::from($status->value)];
        }
        foreach (NotificationTemplateStatusV1::cases() as $status) {
            yield 'template '.$status->value => ['template', new NotificationTemplateResultV1($status), NotificationEventType::TemplateObserved, NotificationEventStatus::from($status->value)];
        }
        foreach (NotificationChannelStatusV1::cases() as $status) {
            yield 'channel '.$status->value => ['channel', new NotificationChannelResultV1($status), NotificationEventType::ChannelObserved, NotificationEventStatus::from($status->value)];
        }
    }
}
