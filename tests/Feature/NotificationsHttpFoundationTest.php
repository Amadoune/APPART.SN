<?php

namespace Tests\Feature;

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
use Tests\TestCase;

final class NotificationsHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(NotificationPreferenceReaderV1::class, new class implements NotificationPreferenceReaderV1
        {
            public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationPreferenceResultV1
            {
                return new NotificationPreferenceResultV1(NotificationPreferenceStatusV1::Enabled);
            }
        });
        $this->app->instance(NotificationTemplateReaderV1::class, new class implements NotificationTemplateReaderV1
        {
            public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationTemplateResultV1
            {
                return new NotificationTemplateResultV1(NotificationTemplateStatusV1::Available);
            }
        });
        $this->app->instance(NotificationChannelReaderV1::class, new class implements NotificationChannelReaderV1
        {
            public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationChannelResultV1
            {
                return new NotificationChannelResultV1(NotificationChannelStatusV1::Allowed);
            }
        });
    }

    public function test_endpoints_pass_contract_inputs_and_return_only_status(): void
    {
        $query = '?subjectKey=subject%3A42&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00';
        $this->getJson('/api/notifications/preference'.$query)->assertOk()->assertExactJson(['status' => 'enabled']);
        $this->getJson('/api/notifications/template'.$query)->assertOk()->assertExactJson(['status' => 'available']);
        $this->getJson('/api/notifications/channel'.$query)->assertOk()->assertExactJson(['status' => 'allowed']);
    }

    public function test_endpoints_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/notifications/preference')->assertUnprocessable();
        $this->getJson('/api/notifications/channel?subjectKey=x&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00&email=secret')->assertUnprocessable();
    }
}
