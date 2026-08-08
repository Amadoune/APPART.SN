<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

use DateTimeImmutable;

interface ExperienceAcceptanceOutboxStore extends ExperienceAcceptanceOutboxReader, ExperienceAcceptanceOutboxWriter
{
    public function claim(ExperienceAcceptanceOutboxMessageId $messageId, DateTimeImmutable $claimedAt): ExperienceAcceptanceOutboxClaimResult;

    public function retry(ExperienceAcceptanceOutboxMessageId $messageId, DateTimeImmutable $availableAt): ExperienceAcceptanceOutboxRetryResult;
}
