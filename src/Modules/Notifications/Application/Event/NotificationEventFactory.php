<?php

namespace Appart\Modules\Notifications\Application\Event;

use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationChannelReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationPreferenceReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationTemplateReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;

final readonly class NotificationEventFactory
{
    public function __construct(
        private NotificationPreferenceReaderV1 $preferenceReader,
        private NotificationTemplateReaderV1 $templateReader,
        private NotificationChannelReaderV1 $channelReader,
    ) {}

    public function preference(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationEventV1
    {
        $status = match ($this->preferenceReader->read($subject, $observedAt)->status) {
            NotificationPreferenceStatusV1::Enabled => NotificationEventStatus::Enabled,
            NotificationPreferenceStatusV1::Disabled => NotificationEventStatus::Disabled,
            NotificationPreferenceStatusV1::Missing => NotificationEventStatus::Missing,
            NotificationPreferenceStatusV1::Corrupted => NotificationEventStatus::Corrupted,
            NotificationPreferenceStatusV1::DependencyUnavailable => NotificationEventStatus::DependencyUnavailable,
        };

        return $this->event(NotificationEventType::PreferenceObserved, $status, $observedAt);
    }

    public function template(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationEventV1
    {
        $status = match ($this->templateReader->read($subject, $observedAt)->status) {
            NotificationTemplateStatusV1::Available => NotificationEventStatus::Available,
            NotificationTemplateStatusV1::Missing => NotificationEventStatus::Missing,
            NotificationTemplateStatusV1::Corrupted => NotificationEventStatus::Corrupted,
            NotificationTemplateStatusV1::DependencyUnavailable => NotificationEventStatus::DependencyUnavailable,
        };

        return $this->event(NotificationEventType::TemplateObserved, $status, $observedAt);
    }

    public function channel(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationEventV1
    {
        $status = match ($this->channelReader->read($subject, $observedAt)->status) {
            NotificationChannelStatusV1::Allowed => NotificationEventStatus::Allowed,
            NotificationChannelStatusV1::Blocked => NotificationEventStatus::Blocked,
            NotificationChannelStatusV1::Missing => NotificationEventStatus::Missing,
            NotificationChannelStatusV1::Corrupted => NotificationEventStatus::Corrupted,
            NotificationChannelStatusV1::DependencyUnavailable => NotificationEventStatus::DependencyUnavailable,
        };

        return $this->event(NotificationEventType::ChannelObserved, $status, $observedAt);
    }

    private function event(NotificationEventType $type, NotificationEventStatus $status, NotificationObservedAt $observedAt): NotificationEventV1
    {
        return new NotificationEventV1($type, new NotificationEventPayload($status, $observedAt->canonical()));
    }
}
