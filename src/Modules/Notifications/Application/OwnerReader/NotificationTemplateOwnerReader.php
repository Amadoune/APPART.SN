<?php

namespace Appart\Modules\Notifications\Application\OwnerReader;

use Appart\Modules\Notifications\Application\OwnerReader\Contract\NotificationOwnerReaderV1;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\PublicRead\Contract\NotificationTemplateReaderV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;

final readonly class NotificationTemplateOwnerReader implements NotificationTemplateReaderV1
{
    public function __construct(private NotificationsOwnerSource $source, private NotificationOwnerReaderV1 $policy) {}

    public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationTemplateResultV1
    {
        $status = match ($this->policy->template($this->source->readTemplate($subject, $observedAt))->status) {
            NotificationOwnerReaderStatus::Available => NotificationTemplateStatusV1::Available,
            NotificationOwnerReaderStatus::Missing => NotificationTemplateStatusV1::Missing,
            NotificationOwnerReaderStatus::Corrupted => NotificationTemplateStatusV1::Corrupted,
            NotificationOwnerReaderStatus::DependencyUnavailable => NotificationTemplateStatusV1::DependencyUnavailable,
            NotificationOwnerReaderStatus::Enabled,
            NotificationOwnerReaderStatus::Disabled,
            NotificationOwnerReaderStatus::Allowed,
            NotificationOwnerReaderStatus::Blocked => throw new \LogicException('Invalid template owner status.'),
        };

        return new NotificationTemplateResultV1($status);
    }
}
