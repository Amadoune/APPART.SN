<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

use DateTimeImmutable;

final readonly class AccountStatusOccurredAt
{
    public function __construct(public DateTimeImmutable $value) {}
}
