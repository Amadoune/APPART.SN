<?php

namespace Appart\Modules\Notifications\Application\PublicRead\Contract;

use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;

interface NotificationChannelReaderV1
{
    public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationChannelResultV1;
}
