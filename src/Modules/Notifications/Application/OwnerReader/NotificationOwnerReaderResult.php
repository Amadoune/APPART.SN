<?php

namespace Appart\Modules\Notifications\Application\OwnerReader;

final readonly class NotificationOwnerReaderResult
{
    public function __construct(public NotificationOwnerReaderStatus $status) {}
}
