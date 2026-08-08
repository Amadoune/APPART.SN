<?php

namespace Appart\Modules\Notifications\Application\OwnerReader;

use Appart\Modules\Notifications\Application\OwnerReader\Contract\NotificationOwnerReaderV1;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationChannelReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;

final readonly class NotificationChannelOwnerReader implements NotificationChannelReaderV1
{
    public function __construct(private NotificationsOwnerSource $source, private NotificationOwnerReaderV1 $policy) {}

    public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationChannelResultV1
    {
        $status = match ($this->policy->channel($this->source->readChannel($subject, $observedAt))->status) {
            NotificationOwnerReaderStatus::Allowed => NotificationChannelStatusV1::Allowed,
            NotificationOwnerReaderStatus::Blocked => NotificationChannelStatusV1::Blocked,
            NotificationOwnerReaderStatus::Missing => NotificationChannelStatusV1::Missing,
            NotificationOwnerReaderStatus::Corrupted => NotificationChannelStatusV1::Corrupted,
            NotificationOwnerReaderStatus::DependencyUnavailable => NotificationChannelStatusV1::DependencyUnavailable,
            NotificationOwnerReaderStatus::Enabled,
            NotificationOwnerReaderStatus::Disabled,
            NotificationOwnerReaderStatus::Available => throw new \LogicException('Invalid channel owner status.'),
        };

        return new NotificationChannelResultV1($status);
    }
}
