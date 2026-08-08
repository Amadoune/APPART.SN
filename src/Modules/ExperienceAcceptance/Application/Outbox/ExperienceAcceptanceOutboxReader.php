<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

use DateTimeImmutable;

interface ExperienceAcceptanceOutboxReader
{
    /** @return list<ExperienceAcceptanceOutboxMessage> */
    public function eligible(DateTimeImmutable $availableAt, int $limit): array;
}
