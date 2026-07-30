<?php

namespace Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract;

use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

interface ModeratorAuthorizationReaderV1
{
    public function authorize(
        AccountId $accountId,
        ModerationCapabilityV1 $capability,
        DateTimeImmutable $observedAt,
    ): ModeratorAuthorizationDecisionV1;
}
