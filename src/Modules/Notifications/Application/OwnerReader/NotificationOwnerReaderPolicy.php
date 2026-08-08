<?php

namespace Appart\Modules\Notifications\Application\OwnerReader;

use Appart\Modules\Notifications\Application\OwnerReader\Contract\NotificationOwnerReaderV1;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateReadResult;

final readonly class NotificationOwnerReaderPolicy implements NotificationOwnerReaderV1
{
    public function preference(NotificationPreferenceReadResult $result): NotificationOwnerReaderResult
    {
        return new NotificationOwnerReaderResult(NotificationOwnerReaderStatus::from($result->status->value));
    }

    public function template(NotificationTemplateReadResult $result): NotificationOwnerReaderResult
    {
        return new NotificationOwnerReaderResult(NotificationOwnerReaderStatus::from($result->status->value));
    }

    public function channel(NotificationChannelReadResult $result): NotificationOwnerReaderResult
    {
        return new NotificationOwnerReaderResult(NotificationOwnerReaderStatus::from($result->status->value));
    }
}
