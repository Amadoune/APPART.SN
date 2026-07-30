<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

abstract readonly class AbstractAccountEvent implements AccountEvent
{
    private AccountEventMetadata $metadata;

    public function __construct(private AccountId $accountId, private DateTimeImmutable $occurredAt)
    {
        $this->metadata = new AccountEventMetadata;
    }

    public function accountId(): AccountId
    {
        return $this->accountId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateVersion(): int
    {
        return $this->metadata->aggregateVersion;
    }

    public function eventIndex(): int
    {
        return $this->metadata->eventIndex;
    }

    final public function stamp(int $aggregateVersion, int $eventIndex): void
    {
        $this->metadata->aggregateVersion = $aggregateVersion;
        $this->metadata->eventIndex = $eventIndex;
    }
}
