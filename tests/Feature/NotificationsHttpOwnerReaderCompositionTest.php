<?php

namespace Tests\Feature;

use Appart\Modules\Notifications\Application\OwnerReader\NotificationChannelOwnerReader;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationPreferenceOwnerReader;
use Appart\Modules\Notifications\Application\OwnerReader\NotificationTemplateOwnerReader;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateReadResult;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationChannelReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationPreferenceReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationTemplateReaderV1;
use Tests\TestCase;

final class NotificationsHttpOwnerReaderCompositionTest extends TestCase
{
    public function test_http_resolves_the_three_certified_owner_readers(): void
    {
        $source = $this->createMock(NotificationsOwnerSource::class);
        $source->method('readPreference')->willReturn(NotificationPreferenceReadResult::missing());
        $source->method('readTemplate')->willReturn(NotificationTemplateReadResult::missing());
        $source->method('readChannel')->willReturn(NotificationChannelReadResult::missing());
        $this->app->instance(NotificationsOwnerSource::class, $source);

        self::assertInstanceOf(NotificationPreferenceOwnerReader::class, $this->app->make(NotificationPreferenceReaderV1::class));
        self::assertInstanceOf(NotificationTemplateOwnerReader::class, $this->app->make(NotificationTemplateReaderV1::class));
        self::assertInstanceOf(NotificationChannelOwnerReader::class, $this->app->make(NotificationChannelReaderV1::class));

        $query = '?subjectKey=subject%3A42&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00';
        $this->getJson('/api/notifications/preference'.$query)->assertNotFound()->assertExactJson(['status' => 'missing']);
        $this->getJson('/api/notifications/template'.$query)->assertNotFound()->assertExactJson(['status' => 'missing']);
        $this->getJson('/api/notifications/channel'.$query)->assertNotFound()->assertExactJson(['status' => 'missing']);
    }
}
