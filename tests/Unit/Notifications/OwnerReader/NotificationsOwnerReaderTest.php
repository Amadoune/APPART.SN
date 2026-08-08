<?php

namespace Tests\Unit\Notifications\OwnerReader;

use Appart\Modules\Notifications\Application\OwnerReader\NotificationChannelOwnerReader;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationOwnerReaderPolicy;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationPreferenceOwnerReader;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationTemplateOwnerReader;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateRevisionState;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotificationsOwnerReaderTest extends TestCase
{
    #[DataProvider('preferenceCases')]
    public function test_preference_reduction_is_mechanical(NotificationPreferenceReadResult $owner, NotificationPreferenceStatusV1 $expected): void
    {
        $source = $this->createMock(NotificationsOwnerSource::class);
        $source->method('readPreference')->willReturn($owner);
        $result = (new NotificationPreferenceOwnerReader($source, new NotificationOwnerReaderPolicy))->read($this->subject(), $this->observedAt());
        self::assertSame($expected, $result->status);
    }

    #[DataProvider('templateCases')]
    public function test_template_reduction_is_mechanical(NotificationTemplateReadResult $owner, NotificationTemplateStatusV1 $expected): void
    {
        $source = $this->createMock(NotificationsOwnerSource::class);
        $source->method('readTemplate')->willReturn($owner);
        self::assertSame($expected, (new NotificationTemplateOwnerReader($source, new NotificationOwnerReaderPolicy))->read($this->subject(), $this->observedAt())->status);
    }

    #[DataProvider('channelCases')]
    public function test_channel_reduction_is_mechanical(NotificationChannelReadResult $owner, NotificationChannelStatusV1 $expected): void
    {
        $source = $this->createMock(NotificationsOwnerSource::class);
        $source->method('readChannel')->willReturn($owner);
        self::assertSame($expected, (new NotificationChannelOwnerReader($source, new NotificationOwnerReaderPolicy))->read($this->subject(), $this->observedAt())->status);
    }

    /** @return iterable<string, array{NotificationPreferenceReadResult, NotificationPreferenceStatusV1}> */
    public static function preferenceCases(): iterable
    {
        $s = new NotificationPreferenceRevisionState('subject:1', 1, NotificationPreferenceStatusV1::Enabled, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        yield 'enabled' => [NotificationPreferenceReadResult::found($s), NotificationPreferenceStatusV1::Enabled];
        yield 'missing' => [NotificationPreferenceReadResult::missing(), NotificationPreferenceStatusV1::Missing];
        yield 'corrupted' => [NotificationPreferenceReadResult::corrupted(), NotificationPreferenceStatusV1::Corrupted];
        yield 'dependency' => [NotificationPreferenceReadResult::dependencyUnavailable(), NotificationPreferenceStatusV1::DependencyUnavailable];
    }

    /** @return iterable<string, array{NotificationTemplateReadResult, NotificationTemplateStatusV1}> */
    public static function templateCases(): iterable
    {
        $s = new NotificationTemplateRevisionState('subject:1', 1, NotificationTemplateStatusV1::Available, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        yield 'available' => [NotificationTemplateReadResult::found($s), NotificationTemplateStatusV1::Available];
        yield 'missing' => [NotificationTemplateReadResult::missing(), NotificationTemplateStatusV1::Missing];
        yield 'corrupted' => [NotificationTemplateReadResult::corrupted(), NotificationTemplateStatusV1::Corrupted];
        yield 'dependency' => [NotificationTemplateReadResult::dependencyUnavailable(), NotificationTemplateStatusV1::DependencyUnavailable];
    }

    /** @return iterable<string, array{NotificationChannelReadResult, NotificationChannelStatusV1}> */
    public static function channelCases(): iterable
    {
        $s = new NotificationChannelRevisionState('subject:1', 1, NotificationChannelStatusV1::Allowed, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        yield 'allowed' => [NotificationChannelReadResult::found($s), NotificationChannelStatusV1::Allowed];
        yield 'missing' => [NotificationChannelReadResult::missing(), NotificationChannelStatusV1::Missing];
        yield 'corrupted' => [NotificationChannelReadResult::corrupted(), NotificationChannelStatusV1::Corrupted];
        yield 'dependency' => [NotificationChannelReadResult::dependencyUnavailable(), NotificationChannelStatusV1::DependencyUnavailable];
    }

    private function subject(): NotificationSubjectKey
    {
        return new NotificationSubjectKey('subject:1');
    }

    private function observedAt(): NotificationObservedAt
    {
        return new NotificationObservedAt(new DateTimeImmutable('2026-08-02T12:00:00Z'));
    }
}
