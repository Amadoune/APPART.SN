<?php

namespace Appart\Modules\Notifications\Application\Outbox;

interface NotificationOutboxReader
{
    /** @return list<NotificationOutboxResult> */
    public function pending(int $limit): array;
}
