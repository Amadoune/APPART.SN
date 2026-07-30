<?php

namespace App\Application\AccountStatusEventIntegration;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use DateTimeImmutable;

final readonly class AccountStatusAtomicEventRequest
{
    public function __construct(
        public AccountStatusContextV1 $context,
        public DateTimeImmutable $recordedAt,
    ) {}
}
