<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

interface AccountEvent
{
    public function accountId(): AccountId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
