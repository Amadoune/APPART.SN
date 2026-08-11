<?php

namespace Tests\Unit\PublicationReview;

use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicationReviewProjectionStore;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicListingProjectionActivation;
use Appart\Modules\PublicationReview\Application\Projection\DeterministicProjectPublishedListing;
use Appart\Modules\PublicationReview\Application\Projection\ProjectionActivationStatus;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingResult;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeterministicProjectPublishedListingTest extends TestCase
{
    public function test_it_expresses_only_a_projection_intention_and_delegates_the_activation(): void
    {
        $projection = new class implements PublicListingProjectionActivation
        {
            public int $calls = 0;

            public function activate(string $listingId): ProjectionActivationStatus
            {
                $this->calls++;

                return ProjectionActivationStatus::Applied;
            }
        };
        $store = new class implements PublicationReviewProjectionStore
        {
            /** @var list<array{string, int, string}> */
            public array $calls = [];

            public function activate(string $listingId, int $publicationVersion, string $commandId, DateTimeImmutable $occurredAt, callable $activation): ProjectPublishedListingResult
            {
                $this->calls[] = [$listingId, $publicationVersion, $commandId];

                return new ProjectPublishedListingResult(
                    $activation() === ProjectionActivationStatus::Applied
                        ? ProjectPublishedListingStatus::Applied
                        : ProjectPublishedListingStatus::Conflict,
                    5,
                );
            }
        };
        $service = new DeterministicProjectPublishedListing($store, $projection);

        $result = $service->project('11111111-1111-4111-8111-111111111111', 9, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', new DateTimeImmutable('2026-08-11T10:00:00+00:00'));

        self::assertSame(ProjectPublishedListingStatus::Applied, $result->status);
        self::assertSame(5, $result->queueVersion);
        self::assertSame(1, $projection->calls);
        self::assertSame([['11111111-1111-4111-8111-111111111111', 9, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']], $store->calls);
    }
}
