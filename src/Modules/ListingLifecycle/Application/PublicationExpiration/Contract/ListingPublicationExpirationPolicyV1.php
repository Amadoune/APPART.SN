<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationExpiration\Contract;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use DateTimeImmutable;

interface ListingPublicationExpirationPolicyV1
{
    public function expirationFor(DateTimeImmutable $publishedAt): ExpirationDate;
}
