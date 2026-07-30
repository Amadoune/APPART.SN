<?php

namespace Tests\Unit\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventId;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventPayload;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventPayloadVersion;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\UnsupportedListingPublicationEventTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDecisionStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ListingPublicationEventContractTest extends TestCase
{
    /** @return iterable<string, array{ListingPublicationState,ListingPublicationAction,ListingPublicationState,ListingPublicationEventType}> */
    public static function transitionEventProvider(): iterable
    {
        $matrix = [
            'draft>submit>submitted' => ListingPublicationEventType::ListingSubmitted,
            'draft>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
            'draft>archive>archived' => ListingPublicationEventType::ListingArchived,
            'submitted>begin_review>under_review' => ListingPublicationEventType::ListingReviewStarted,
            'submitted>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
            'under_review>approve_and_publish>published' => ListingPublicationEventType::ListingPublished,
            'under_review>request_changes>changes_requested' => ListingPublicationEventType::ListingChangesRequested,
            'under_review>reject>rejected' => ListingPublicationEventType::ListingRejected,
            'under_review>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
            'changes_requested>submit>submitted' => ListingPublicationEventType::ListingResubmitted,
            'changes_requested>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
            'changes_requested>archive>archived' => ListingPublicationEventType::ListingArchived,
            'published>review_material_change>under_review' => ListingPublicationEventType::ListingMaterialChangeReviewStarted,
            'published>suspend>suspended' => ListingPublicationEventType::ListingSuspended,
            'published>expire>expired' => ListingPublicationEventType::ListingExpired,
            'published>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
            'suspended>reinstate>published' => ListingPublicationEventType::ListingReinstated,
            'suspended>request_changes>changes_requested' => ListingPublicationEventType::ListingChangesRequested,
            'suspended>reject>rejected' => ListingPublicationEventType::ListingRejected,
            'suspended>archive>archived' => ListingPublicationEventType::ListingArchived,
            'expired>review_renewal>under_review' => ListingPublicationEventType::ListingRenewalReviewStarted,
            'expired>renew_directly>published' => ListingPublicationEventType::ListingRenewed,
            'expired>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
            'expired>archive>archived' => ListingPublicationEventType::ListingArchived,
            'withdrawn>approve_republication>under_review' => ListingPublicationEventType::ListingRepublicationReviewStarted,
            'withdrawn>archive>archived' => ListingPublicationEventType::ListingArchived,
            'rejected>archive>archived' => ListingPublicationEventType::ListingArchived,
        ];
        foreach ($matrix as $transition => $eventType) {
            [$from, $action, $to] = explode('>', $transition);
            yield $transition => [ListingPublicationState::from($from), ListingPublicationAction::from($action), ListingPublicationState::from($to), $eventType];
        }
    }

    #[DataProvider('transitionEventProvider')]
    public function test_every_certified_transition_has_one_exact_event(ListingPublicationState $from, ListingPublicationAction $action, ListingPublicationState $to, ListingPublicationEventType $expected): void
    {
        $events = (new ListingPublicationEventCatalog)->eventsFor($this->listingId(), new ListingPublicationTransition($from, $to, $action), 2, $this->metadata());

        self::assertCount(1, $events);
        self::assertSame($expected, $events[0]->type);
        self::assertSame(1, $events[0]->payloadVersion->value);
        self::assertSame([$this->listingId()->value, $from->value, $to->value, $action->value, 2], array_values($events[0]->payload->fields()));
    }

    public function test_catalog_and_workflow_cover_exactly_the_same_twenty_seven_transitions(): void
    {
        $workflow = new ListingPublicationWorkflow;
        $allowed = 0;
        foreach (ListingPublicationState::cases() as $state) {
            foreach (ListingPublicationAction::cases() as $action) {
                $decision = $workflow->decide($state, $action);
                if ($decision->status === ListingPublicationDecisionStatus::Allowed) {
                    $allowed++;
                    self::assertCount(1, (new ListingPublicationEventCatalog)->eventsFor($this->listingId(), $decision->transition, 2, $this->metadata()));
                }
            }
        }

        self::assertSame(27, $allowed);
        self::assertSame($allowed, (new ListingPublicationEventCatalog)->transitionCount());
    }

    public function test_event_identity_payload_metadata_and_serialization_are_deterministic(): void
    {
        $catalog = new ListingPublicationEventCatalog;
        $transition = new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit);
        $first = $catalog->eventsFor($this->listingId(), $transition, 2, $this->metadata())[0];
        $second = $catalog->eventsFor($this->listingId(), $transition, 2, $this->metadata())[0];

        self::assertEquals($first, $second);
        self::assertSame((new ListingPublicationEventSerializer)->serialize($first), (new ListingPublicationEventSerializer)->serialize($second));
        self::assertSame('2026-07-20T10:00:00.000000Z', $first->metadata->occurredAt->value);
        self::assertSame('2026-07-20T10:00:01.000000Z', $first->metadata->recordedAt->value);
    }

    public function test_serialization_shape_is_canonical_and_complete(): void
    {
        $transition = new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit);
        $event = (new ListingPublicationEventCatalog)->eventsFor($this->listingId(), $transition, 2, $this->metadata())[0];
        $decoded = json_decode((new ListingPublicationEventSerializer)->serialize($event), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['listingId', 'previousState', 'state', 'action', 'publicationVersion'], array_keys($decoded['payload']));
        self::assertSame(['occurredAt', 'recordedAt'], array_keys($decoded['metadata']));
    }

    public function test_model_is_closed_versioned_and_immutable(): void
    {
        self::assertCount(15, ListingPublicationEventType::cases());
        self::assertSame([ListingPublicationEventPayloadVersion::V1], ListingPublicationEventPayloadVersion::cases());
        foreach ([ListingPublicationEvent::class, ListingPublicationEventId::class, ListingPublicationEventInstant::class, ListingPublicationEventMetadata::class, ListingPublicationEventPayload::class, ListingPublicationEventCatalog::class, ListingPublicationEventSerializer::class] as $class) {
            self::assertTrue((new ReflectionClass($class))->isReadOnly(), $class);
        }
    }

    public function test_metadata_must_be_explicit_canonical_and_causally_ordered(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ListingPublicationEventMetadata(
            ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'),
            ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
        );
    }

    public function test_non_canonical_instant_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20 10:00:00');
    }

    public function test_uncertified_transition_is_rejected_without_implicit_mapping(): void
    {
        $this->expectException(UnsupportedListingPublicationEventTransition::class);
        (new ListingPublicationEventCatalog)->eventsFor(
            $this->listingId(),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Published, ListingPublicationAction::Submit),
            2,
            $this->metadata(),
        );
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('11111111-1111-4111-8111-111111111111');
    }

    private function metadata(): ListingPublicationEventMetadata
    {
        return new ListingPublicationEventMetadata(
            ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
            ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'),
        );
    }
}
