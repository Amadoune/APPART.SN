<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountAvailabilityPurpose;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountAvailabilityResult;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

interface AccountAvailabilityInspector
{
    public function inspect(
        AccountId $accountId,
        AccountAvailabilityPurpose $purpose,
        DateTimeImmutable $observedAt,
    ): AccountAvailabilityResult;
}
