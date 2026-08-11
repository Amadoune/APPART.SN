<?php

namespace Tests\Unit\PublicationReview;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItem;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItemState;
use Appart\Modules\PublicationReview\Application\Review\Contract\PublicationReviewCommandStore;
use Appart\Modules\PublicationReview\Application\Review\DeterministicPublicationReviewCommands;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandResult;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeterministicPublicationReviewCommandsTest extends TestCase
{
    public function test_begin_and_approve_delegate_only_queue_owned_values_to_the_gateway(): void
    {
        $item = new PublicationReviewQueueItem(
            'queue-1',
            'event-1',
            '11111111-1111-4111-8111-111111111111',
            7,
            new DateTimeImmutable('2026-08-11T08:00:00+00:00'),
            PublicationReviewQueueItemState::Claimed,
            2,
            'reviewer-1',
        );
        $store = new class($item) implements PublicationReviewCommandStore
        {
            /** @var list<array{string, bool}> */
            public array $calls = [];

            public function __construct(private PublicationReviewQueueItem $item) {}

            public function execute(string $operation, string $queueItemId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt, bool $complete, callable $gateway): PublicationReviewCommandResult
            {
                $this->calls[] = [$operation, $complete];
                $result = $gateway($this->item);

                return new PublicationReviewCommandResult(
                    $result->status === ListingPublicationCommandStatus::Applied
                        ? PublicationReviewCommandStatus::Applied
                        : PublicationReviewCommandStatus::GatewayRejected,
                    $expectedVersion + 1,
                );
            }
        };
        $gateway = new class implements ListingPublicationCommandGatewayV1
        {
            /** @var list<array{string, string, int, string}> */
            public array $calls = [];

            public function beginReview(string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1
            {
                $this->calls[] = ['begin', $listingId, $expectedVersion, $actor];

                return new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::Applied);
            }

            public function approveAndPublish(string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1
            {
                $this->calls[] = ['approve', $listingId, $expectedVersion, $actor];

                return new ListingPublicationCommandResultV1(ListingPublicationCommandStatus::Applied);
            }
        };
        $commands = new DeterministicPublicationReviewCommands($store, $gateway);
        $at = new DateTimeImmutable('2026-08-11T09:00:00+00:00');

        self::assertSame(PublicationReviewCommandStatus::Applied, $commands->begin('queue-1', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 2, 'reviewer-1', $at)->status);
        self::assertSame(PublicationReviewCommandStatus::Applied, $commands->approve('queue-1', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 3, 'reviewer-1', $at)->status);
        self::assertSame([['begin_review', false], ['approve_and_publish', true]], $store->calls);
        self::assertSame([
            ['begin', $item->listingId, 7, 'reviewer-1'],
            ['approve', $item->listingId, 8, 'reviewer-1'],
        ], $gateway->calls);
    }
}
