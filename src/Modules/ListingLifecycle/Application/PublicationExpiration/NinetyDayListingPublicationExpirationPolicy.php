<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationExpiration;

use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\Contract\ListingPublicationExpirationPolicyV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use DateTimeImmutable;

final readonly class NinetyDayListingPublicationExpirationPolicy implements ListingPublicationExpirationPolicyV1
{
    public function expirationFor(DateTimeImmutable $publishedAt): ExpirationDate
    {
        return ExpirationDate::fromDateTime($publishedAt->modify('+90 days'));
    }
}
