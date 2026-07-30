<?php

namespace Appart\Modules\IdentityAccess\Domain\Event;

final class AccountEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
