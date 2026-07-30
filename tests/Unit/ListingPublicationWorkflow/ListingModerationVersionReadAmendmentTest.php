<?php

namespace Tests\Unit\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListingModerationVersionReadAmendmentTest extends TestCase
{
    #[Test]
    public function owner_store_read_result_provides_the_expected_version_without_new_contract(): void
    {
        $listingId = ListingId::fromString('11111111-1111-4111-8111-111111111111');
        $result = ListingPublicationPersistenceReadResult::found(
            new ListingPublicationStoredState($listingId, ListingPublicationState::Draft, 7),
        );

        self::assertSame(ListingPublicationPersistenceReadStatus::Found, $result->status);
        self::assertNotNull($result->snapshot);
        self::assertSame(7, $result->snapshot->version);
    }

    #[Test]
    public function missing_and_corrupted_reads_expose_no_version(): void
    {
        $listingId = ListingId::fromString('11111111-1111-4111-8111-111111111111');

        foreach ([
            ListingPublicationPersistenceReadResult::missing($listingId),
            ListingPublicationPersistenceReadResult::corrupted($listingId),
        ] as $result) {
            self::assertNull($result->snapshot);
        }
    }
}
