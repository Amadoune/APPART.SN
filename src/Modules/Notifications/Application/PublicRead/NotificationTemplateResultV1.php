<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

final readonly class NotificationTemplateResultV1
{
    public function __construct(public NotificationTemplateStatusV1 $status) {}
}
