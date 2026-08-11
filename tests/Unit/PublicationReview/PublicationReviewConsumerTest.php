<?php

namespace Tests\Unit\PublicationReview;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueue;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewConsumer;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewIngestionResult;
use PHPUnit\Framework\TestCase;

final class PublicationReviewConsumerTest extends TestCase
{
    public function test_it_delegates_the_immutable_submitted_event_to_the_owner_queue(): void
    {
        $event = $this->submittedEvent();
        $queue = new RecordingPublicationReviewQueue;

        self::assertSame(PublicationReviewIngestionResult::Applied, (new PublicationReviewConsumer($queue))->consume($event));
        self::assertSame($event, $queue->event);
    }

    private function submittedEvent(): ListingPublicationEvent
    {
        $transition = new ListingPublicationTransition(
            ListingPublicationState::Draft,
            ListingPublicationState::Submitted,
            ListingPublicationAction::Submit,
        );
        $metadata = new ListingPublicationEventMetadata(
            ListingPublicationEventInstant::fromCanonicalUtc('2026-08-10T10:00:00.000000Z'),
            ListingPublicationEventInstant::fromCanonicalUtc('2026-08-10T10:00:00.000000Z'),
        );

        return (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString('11111111-1111-4111-8111-111111111111'),
            $transition,
            2,
            $metadata,
        )[0];
    }
}

final class RecordingPublicationReviewQueue implements PublicationReviewQueue
{
    public ?ListingPublicationEvent $event = null;

    public function ingest(ListingPublicationEvent $event): PublicationReviewIngestionResult
    {
        $this->event = $event;

        return PublicationReviewIngestionResult::Applied;
    }
}
