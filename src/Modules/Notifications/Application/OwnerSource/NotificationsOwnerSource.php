<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;

interface NotificationsOwnerSource
{
    public function appendPreference(NotificationPreferenceRevisionState $revision): NotificationPreferenceWriteResult;

    public function appendTemplate(NotificationTemplateRevisionState $revision): NotificationTemplateWriteResult;

    public function appendChannel(NotificationChannelRevisionState $revision): NotificationChannelWriteResult;

    public function readPreference(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationPreferenceReadResult;

    public function readTemplate(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationTemplateReadResult;

    public function readChannel(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationChannelReadResult;
}
