<?php

namespace Tests\Unit\Modules\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\Exception\TransitionConditionNotSatisfied;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OptionalTransitionReasonTest extends TestCase
{
    #[DataProvider('reasonlessTransitions')]
    public function test_only_certified_transitions_accept_no_reason(ListingStatus $from, ListingStatus $to, TransitionTrigger $trigger, TransitionOrigin $origin): void
    {
        (new ListingTransitionPolicy)->assertAllowed($from, $to, $this->evidence($trigger, $origin), PropertyAvailability::Eligible);
        self::addToAssertionCount(1);
    }

    public function test_other_transition_remains_strict(): void
    {
        $this->expectException(TransitionConditionNotSatisfied::class);
        (new ListingTransitionPolicy)->assertAllowed(
            ListingStatus::Published,
            ListingStatus::Withdrawn,
            $this->evidence(TransitionTrigger::VoluntaryWithdrawal, TransitionOrigin::Advertiser),
            PropertyAvailability::Eligible,
        );
    }

    public function test_reasonless_submit_is_preserved_in_revision_and_event_without_rewriting_history(): void
    {
        $policy = new ListingTransitionPolicy;
        $draftReason = TransitionReason::fromString('The advertiser intentionally created a draft.');
        $draftEvidence = new TransitionEvidence(
            ActorId::fromString('actor:model-simplification'),
            TransitionTrigger::DraftStarted,
            $draftReason,
            TransitionOrigin::Advertiser,
            new DateTimeImmutable('2026-08-09T09:00:00+00:00'),
        );
        $listing = Listing::createDraft(
            ListingId::fromString('32000000-0000-4000-8000-000000000001'),
            PropertyId::fromString('31000000-0000-4000-8000-000000000001'),
            ListingRevisionId::fromString('32000000-0000-4000-8000-000000000011'),
            $draftEvidence,
            $policy,
            PropertyAvailability::Eligible,
        );
        $listing->releaseEvents();

        $listing->submit(
            ListingRevisionId::fromString('32000000-0000-4000-8000-000000000012'),
            $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser),
            $policy,
            PropertyAvailability::Eligible,
        );

        self::assertSame($draftReason->value, $listing->revisions()[0]->reason?->value);
        self::assertNull($listing->revisions()[1]->reason);
        $events = $listing->releaseEvents();
        self::assertCount(1, $events);
        self::assertNull($events[0]->reason());
    }

    /** @return iterable<string, array{ListingStatus, ListingStatus, TransitionTrigger, TransitionOrigin}> */
    public static function reasonlessTransitions(): iterable
    {
        yield 'submit' => [ListingStatus::Draft, ListingStatus::Submitted, TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser];
        yield 'begin review' => [ListingStatus::Submitted, ListingStatus::UnderReview, TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation];
        yield 'approve and publish' => [ListingStatus::UnderReview, ListingStatus::Published, TransitionTrigger::FavorableReview, TransitionOrigin::Moderation];
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor:model-simplification'), $trigger, null, $origin, new DateTimeImmutable('2026-08-09T10:00:00+00:00'));
    }
}
