<?php

namespace Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract;

use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewAuthorizationResult;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewCapabilityV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

interface PublicationReviewAuthorizationReaderV1
{
    public function authorize(AccountId $accountId, PublicationReviewCapabilityV1 $capability, DateTimeImmutable $observedAt): PublicationReviewAuthorizationResult;
}
