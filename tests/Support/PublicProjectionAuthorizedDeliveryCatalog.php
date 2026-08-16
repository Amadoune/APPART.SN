<?php

namespace Tests\Support;

use PHPUnit\Framework\Assert;

final class PublicProjectionAuthorizedDeliveryCatalog
{
    public const RENAMED_PLACE_KEY = 'public-projection:place.lifecycle.renamed:1';

    /** @var list<string> */
    private const KEYS = [
        'public-projection:account.status.reactivated:1',
        'public-projection:account.status.suspended:1',
        'public-projection:administrative.action.lifecycle.approval_requested:1',
        'public-projection:administrative.action.lifecycle.approved:1',
        'public-projection:administrative.action.lifecycle.recorded:1',
        'public-projection:administrative.action.lifecycle.rejected:1',
        'public-projection:content_seo.reconstruction.requested:1',
        'public-projection:lead.lifecycle.closed:1',
        'public-projection:lead.lifecycle.delivered:1',
        'public-projection:lead.lifecycle.rejected:1',
        'public-projection:listing.publication.archived:1',
        'public-projection:listing.publication.changes_requested:1',
        'public-projection:listing.publication.expired:1',
        'public-projection:listing.publication.material_change_review_started:1',
        'public-projection:listing.publication.published:1',
        'public-projection:listing.publication.reinstated:1',
        'public-projection:listing.publication.rejected:1',
        'public-projection:listing.publication.renewal_review_started:1',
        'public-projection:listing.publication.renewed:1',
        'public-projection:listing.publication.republication_review_started:1',
        'public-projection:listing.publication.resubmitted:1',
        'public-projection:listing.publication.review_started:1',
        'public-projection:listing.publication.submitted:1',
        'public-projection:listing.publication.suspended:1',
        'public-projection:listing.publication.withdrawn:1',
        'public-projection:listing.reconstruction.requested:1',
        'public-projection:media.item.lifecycle.archived:1',
        'public-projection:media.item.lifecycle.removed:1',
        'public-projection:media.reconstruction.requested:1',
        'public-projection:place.lifecycle.disabled:1',
        'public-projection:place.lifecycle.enabled:1',
        'public-projection:place.lifecycle.merged:1',
        self::RENAMED_PLACE_KEY,
        'public-projection:professional.status.reactivated:1',
        'public-projection:professional.status.suspended:1',
        'public-projection:property.lifecycle.activated:1',
        'public-projection:property.lifecycle.archived:1',
        'public-projection:property.lifecycle.availability_restored:1',
        'public-projection:property.lifecycle.decommissioned:1',
        'public-projection:property.lifecycle.maintenance_completed:1',
        'public-projection:property.lifecycle.maintenance_started:1',
        'public-projection:property.lifecycle.marked_unavailable:1',
        'public-projection:property.reconstruction.requested:1',
        'public-projection:reservation.lifecycle.cancelled_from_confirmed:1',
        'public-projection:reservation.lifecycle.cancelled_from_draft:1',
        'public-projection:reservation.lifecycle.cancelled_from_requested:1',
        'public-projection:reservation.lifecycle.cancelled_in_progress:1',
        'public-projection:reservation.lifecycle.completed:1',
        'public-projection:reservation.lifecycle.confirmed:1',
        'public-projection:reservation.lifecycle.expired_from_confirmed:1',
        'public-projection:reservation.lifecycle.expired_from_requested:1',
        'public-projection:reservation.lifecycle.rejected:1',
        'public-projection:reservation.lifecycle.started:1',
        'public-projection:reservation.lifecycle.submitted:1',
        'public-projection:search.reconstruction.requested:1',
    ];

    /** @param list<object> $registrations */
    public static function assertMatches(array $registrations): void
    {
        $actual = array_map(
            static fn (object $registration): string => $registration->consumerId->value.':'.$registration->eventType->value.':'.$registration->payloadVersion->value,
            $registrations,
        );
        $unique = array_values(array_unique($actual));
        $expected = self::KEYS;
        sort($actual);
        sort($unique);
        sort($expected);

        Assert::assertCount(55, $registrations, 'The authorized registry contains exactly 55 raw registrations.');
        Assert::assertSame(count($actual), count($unique), 'Duplicate Public Projection registration keys detected.');
        Assert::assertSame([], array_values(array_diff($expected, $unique)), 'Authorized Public Projection registration keys are missing.');
        Assert::assertSame([], array_values(array_diff($unique, $expected)), 'Unexpected Public Projection registration keys were admitted.');
        Assert::assertSame($expected, $unique, 'The actual Public Projection registry must equal the authorized catalog.');
        Assert::assertContains(self::RENAMED_PLACE_KEY, $unique, 'The certified place rename handoff must remain admitted.');

        $historical = array_values(array_diff($expected, [self::RENAMED_PLACE_KEY]));
        Assert::assertCount(54, $historical);
        Assert::assertSame([], array_values(array_diff($historical, $unique)), 'A historical authorized registration was removed.');
    }
}
