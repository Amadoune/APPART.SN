<?php

namespace Appart\Modules\Notifications\Application\PublicRead\Contract;

use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateResultV1;

interface NotificationTemplateReaderV1
{
    public function read(NotificationSubjectKey $subject, NotificationObservedAt $observedAt): NotificationTemplateResultV1;
}
