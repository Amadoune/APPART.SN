<?php

namespace Tests\Unit\ListingCreation;

use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftCommandV1;
use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftResultV1;
use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftStatusV1;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateListingDraftV1ContractTest extends TestCase
{
    #[Test]
    public function checksum_is_deterministic_and_covers_the_complete_command(): void
    {
        $first = $this->command();
        $same = $this->command();
        $different = new CreateListingDraftCommandV1(
            $first->intentId,
            $first->listingId,
            '55000000-0000-4000-8000-000000000099',
            $first->revisionId,
            $first->actorId,
            $first->occurredAt,
        );

        self::assertSame($first->checksum(), $same->checksum());
        self::assertNotSame($first->checksum(), $different->checksum());
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first->checksum());
    }

    #[Test]
    public function command_rejects_invalid_identifiers_without_exposing_infrastructure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CreateListingDraftCommandV1(
            'not-an-intent',
            '55000000-0000-4000-8000-000000000002',
            '55000000-0000-4000-8000-000000000003',
            '55000000-0000-4000-8000-000000000004',
            'account:owner',
            new DateTimeImmutable('2026-07-27T14:00:00+00:00'),
        );
    }

    #[Test]
    public function results_are_closed_and_never_expose_an_aggregate(): void
    {
        $applied = CreateListingDraftResultV1::applied(
            '55000000-0000-4000-8000-000000000002',
            '55000000-0000-4000-8000-000000000003',
            0,
        );
        $divergent = CreateListingDraftResultV1::failed(
            CreateListingDraftStatusV1::DivergentIntent,
            'intent_divergence',
        );

        self::assertSame(CreateListingDraftStatusV1::Applied, $applied->status);
        self::assertSame(0, $applied->aggregateVersion);
        self::assertSame(CreateListingDraftStatusV1::DivergentIntent, $divergent->status);
        self::assertNull($divergent->listingId);
    }

    private function command(): CreateListingDraftCommandV1
    {
        return new CreateListingDraftCommandV1(
            '55000000-0000-4000-8000-000000000001',
            '55000000-0000-4000-8000-000000000002',
            '55000000-0000-4000-8000-000000000003',
            '55000000-0000-4000-8000-000000000004',
            'account:owner',
            new DateTimeImmutable('2026-07-27T14:00:00+00:00'),
        );
    }
}
