<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

use DateTimeImmutable;

interface ReliabilityOperationsOutboxReader
{
    /** @return list<ReliabilityOperationsOutboxMessage> */
    public function eligible(DateTimeImmutable $availableAt, int $limit): array;
}
