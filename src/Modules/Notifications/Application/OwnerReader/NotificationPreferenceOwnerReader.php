<?php

namespace Appart\Modules\Notifications\Application\OwnerReader;

use Appart\Modules\Notifications\Application\OwnerReader\Contract\NotificationOwnerReaderV1;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationPreferenceReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;

final readonly class NotificationPreferenceOwnerReader implements NotificationPreferenceReaderV1
{
    public function __construct(private NotificationsOwnerSource $source, private NotificationOwnerReaderV1 $policy) {}

    public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationPreferenceResultV1
    {
        $status = match ($this->policy->preference($this->source->readPreference($subject, $observedAt))->status) {
            NotificationOwnerReaderStatus::Enabled => NotificationPreferenceStatusV1::Enabled,
            NotificationOwnerReaderStatus::Disabled => NotificationPreferenceStatusV1::Disabled,
            NotificationOwnerReaderStatus::Missing => NotificationPreferenceStatusV1::Missing,
            NotificationOwnerReaderStatus::Corrupted => NotificationPreferenceStatusV1::Corrupted,
            NotificationOwnerReaderStatus::DependencyUnavailable => NotificationPreferenceStatusV1::DependencyUnavailable,
            NotificationOwnerReaderStatus::Available,
            NotificationOwnerReaderStatus::Allowed,
            NotificationOwnerReaderStatus::Blocked => throw new \LogicException('Invalid preference owner status.'),
        };

        return new NotificationPreferenceResultV1($status);
    }
}
