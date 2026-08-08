<?php

namespace Appart\Modules\Notifications\Application\OwnerReader\Contract;

use Appart\Modules\Notifications\Application\OwnerReader\NotificationOwnerReaderResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateReadResult;

interface NotificationOwnerReaderV1
{
    public function preference(NotificationPreferenceReadResult $result): NotificationOwnerReaderResult;

    public function template(NotificationTemplateReadResult $result): NotificationOwnerReaderResult;

    public function channel(NotificationChannelReadResult $result): NotificationOwnerReaderResult;
}
