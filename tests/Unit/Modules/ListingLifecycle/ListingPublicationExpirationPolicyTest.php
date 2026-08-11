<?php

namespace Tests\Unit\Modules\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Application\PublicationExpiration\NinetyDayListingPublicationExpirationPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ListingPublicationExpirationPolicyTest extends TestCase
{
    public function test_initial_publication_expires_exactly_ninety_calendar_days_later(): void
    {
        $publishedAt = new DateTimeImmutable('2026-08-09T14:30:00+00:00');

        self::assertSame(
            '2026-11-07T14:30:00.000000+00:00',
            (new NinetyDayListingPublicationExpirationPolicy)->expirationFor($publishedAt)->value->format('Y-m-d\TH:i:s.uP'),
        );
    }
}
