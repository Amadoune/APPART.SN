<?php

namespace Appart\Modules\Notifications\Application\PublicRead\Contract;

use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;

interface NotificationPreferenceReaderV1
{
    public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationPreferenceResultV1;
}
