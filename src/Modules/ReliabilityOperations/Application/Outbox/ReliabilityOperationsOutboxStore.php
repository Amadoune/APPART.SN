<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

use DateTimeImmutable;

interface ReliabilityOperationsOutboxStore extends ReliabilityOperationsOutboxReader, ReliabilityOperationsOutboxWriter
{
    public function claim(ReliabilityOperationsOutboxMessageId $messageId, DateTimeImmutable $claimedAt): ReliabilityOperationsOutboxClaimResult;

    public function retry(ReliabilityOperationsOutboxMessageId $messageId, DateTimeImmutable $availableAt): ReliabilityOperationsOutboxRetryResult;
}
