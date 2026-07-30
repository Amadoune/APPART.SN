<?php

namespace Tests\Unit\Modules\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\Event\ListingDraftCreated;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingPublished;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingViolation;
use Appart\Modules\ListingLifecycle\Domain\Exception\TransitionConditionNotSatisfied;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\RenewalRoute;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ListingTest extends TestCase
{
    private int $sequence = 1;

    private ListingTransitionPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new ListingTransitionPolicy;
    }

    public function test_creation_records_complete_immutable_proof(): void
    {
        $listing = $this->draft();
        $revision = $listing->revisions()[0];
        $event = $listing->releaseEvents()[0];
        self::assertSame('actor-1', $revision->actorId->value);
        self::assertSame(TransitionTrigger::DraftStarted, $revision->trigger);
        self::assertSame('Création du brouillon', $revision->reason->value);
        self::assertSame(TransitionOrigin::Advertiser, $revision->origin);
        self::assertInstanceOf(ListingDraftCreated::class, $event);
        self::assertSame($revision->actorId, $event->actorId());
        self::assertSame($revision->trigger, $event->trigger());
        self::assertSame($revision->reason, $event->reason());
        self::assertSame($revision->origin, $event->origin());
        self::assertSame($revision->occurredAt, $event->occurredAt());
    }

    #[DataProvider('validTransitions')]
    public function test_all_27_transitions_require_and_accept_normative_evidence(ListingStatus $from, ListingStatus $to): void
    {
        $listing = $this->listingIn($from);
        $listing->releaseEvents();
        $this->transition($listing, $to, 500);
        $revision = $listing->revisions()[array_key_last($listing->revisions())];
        $event = $listing->releaseEvents()[0];
        self::assertSame($to, $listing->status());
        self::assertSame($from, $revision->previousStatus);
        self::assertSame($to, $revision->status);
        self::assertSame($revision->actorId, $event->actorId());
        self::assertSame($revision->trigger, $event->trigger());
        self::assertSame($revision->reason, $event->reason());
        self::assertSame($revision->origin, $event->origin());
        self::assertSame($revision->occurredAt, $event->occurredAt());
    }

    /** @return array<string, array{ListingStatus, ListingStatus}> */
    public static function validTransitions(): array
    {
        $map = [
            'draft' => ['submitted', 'withdrawn', 'archived'], 'submitted' => ['under_review', 'withdrawn'],
            'under_review' => ['published', 'changes_requested', 'rejected', 'withdrawn'], 'changes_requested' => ['submitted', 'withdrawn', 'archived'],
            'published' => ['under_review', 'suspended', 'expired', 'withdrawn'], 'suspended' => ['published', 'changes_requested', 'rejected', 'archived'],
            'expired' => ['under_review', 'published', 'withdrawn', 'archived'], 'withdrawn' => ['under_review', 'archived'], 'rejected' => ['archived'],
        ];
        $result = [];
        foreach ($map as $from => $targets) {
            foreach ($targets as $to) {
                $result["{$from}_{$to}"] = [ListingStatus::from($from), ListingStatus::from($to)];
            }
        }

        return $result;
    }

    #[DataProvider('invalidTransitions')]
    public function test_forbidden_transitions_leave_no_mutation(ListingStatus $from, ListingStatus $to): void
    {
        $listing = $this->listingIn($from);
        $listing->releaseEvents();
        $version = $listing->version();
        $revisions = $listing->revisions();
        try {
            $this->invoke($listing, $to, $this->evidence(TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser, 500));
            self::fail('Forbidden transition accepted.');
        } catch (ListingViolation|TransitionConditionNotSatisfied) {
            self::assertSame($from, $listing->status());
            self::assertSame($version, $listing->version());
            self::assertSame($revisions, $listing->revisions());
            self::assertSame([], $listing->releaseEvents());
        }
    }

    /** @return array<string, array{ListingStatus, ListingStatus}> */
    public static function invalidTransitions(): array
    {
        $allowed = [];
        foreach (self::validTransitions() as [$f,$t]) {
            $allowed[$f->value.'>'.$t->value] = true;
        } $result = [];
        foreach (ListingStatus::cases() as $f) {
            foreach (ListingStatus::cases() as $t) {
                $key = $f->value.'>'.$t->value;
                if ($t !== ListingStatus::Draft && ! isset($allowed[$key])) {
                    $result[str_replace('>', '_', $key)] = [$f, $t];
                }
            }
        }

        return $result;
    }

    public function test_wrong_archiving_condition_is_refused_without_mutation(): void
    {
        $listing = $this->draft();
        $listing->releaseEvents();
        $version = $listing->version();
        $this->expectException(TransitionConditionNotSatisfied::class);
        try {
            $listing->archive($this->revision(), $this->evidence(TransitionTrigger::FinalClosureConfirmed, TransitionOrigin::Administration, 10), $this->policy, PropertyAvailability::Eligible);
        } finally {
            self::assertSame(ListingStatus::Draft, $listing->status());
            self::assertSame($version, $listing->version());
            self::assertSame([], $listing->releaseEvents());
        }
    }

    public function test_direct_renewal_replaces_expiration_and_preserves_complete_event(): void
    {
        $listing = $this->listingIn(ListingStatus::Expired);
        $listing->releaseEvents();
        $this->transition($listing, ListingStatus::Published, 500);
        $event = $listing->releaseEvents()[0];
        self::assertInstanceOf(ListingPublished::class, $event);
        self::assertEquals($this->at(600), $listing->expirationDate()?->value);
        self::assertSame(TransitionTrigger::DirectRenewalApproved, $event->trigger());
    }

    public function test_ineligible_property_prohibits_both_renewal_routes(): void
    {
        $listing = $this->listingIn(ListingStatus::Expired);
        $listing->releaseEvents();
        $version = $listing->version();
        $this->expectException(TransitionConditionNotSatisfied::class);
        try {
            $listing->publish($this->revision(), ExpirationDate::fromDateTime($this->at(600)), $this->evidenceFor(ListingStatus::Expired, ListingStatus::Published, 500), $this->policy, PropertyAvailability::Ineligible);
        } finally {
            self::assertSame(ListingStatus::Expired, $listing->status());
            self::assertSame($version, $listing->version());
            self::assertSame([], $listing->releaseEvents());
        }
    }

    public function test_renewal_policy_distinguishes_direct_review_and_prohibited_routes(): void
    {
        self::assertSame(RenewalRoute::DirectPublication, $this->policy->renewalRoute($this->evidence(TransitionTrigger::DirectRenewalApproved, TransitionOrigin::Advertiser, 500), PropertyAvailability::Eligible));
        self::assertSame(RenewalRoute::NewReview, $this->policy->renewalRoute($this->evidence(TransitionTrigger::RenewalReviewRequired, TransitionOrigin::Moderation, 500), PropertyAvailability::Eligible));
        self::assertSame(RenewalRoute::Prohibited, $this->policy->renewalRoute($this->evidence(TransitionTrigger::DirectRenewalApproved, TransitionOrigin::Advertiser, 500), PropertyAvailability::Unavailable));
    }

    private function draft(): Listing
    {
        $e = $this->evidence(TransitionTrigger::DraftStarted, TransitionOrigin::Advertiser, 0, 'Création du brouillon');

        return Listing::createDraft($this->listingId(), $this->propertyId(), $this->revision(), $e, $this->policy, PropertyAvailability::Eligible);
    }

    private function listingIn(ListingStatus $status): Listing
    {
        $l = $this->draft();
        $paths = [
            'submitted' => ['submitted'], 'under_review' => ['submitted', 'under_review'], 'changes_requested' => ['submitted', 'under_review', 'changes_requested'],
            'published' => ['submitted', 'under_review', 'published'], 'suspended' => ['submitted', 'under_review', 'published', 'suspended'],
            'expired' => ['submitted', 'under_review', 'published', 'expired'], 'withdrawn' => ['withdrawn'], 'rejected' => ['submitted', 'under_review', 'rejected'], 'archived' => ['archived']];
        foreach ($paths[$status->value] ?? [] as $target) {
            $this->transition($l, ListingStatus::from($target), match ($target) {
                'submitted' => 10,'under_review' => 20,'changes_requested','rejected' => 30,'published' => 50,'suspended' => 60,'expired' => 200,'withdrawn','archived' => 10
            });
        }

        return $l;
    }

    private function transition(Listing $listing, ListingStatus $to, int $minute): void
    {
        $this->invoke($listing, $to, $this->evidenceFor($listing->status(), $to, $minute));
    }

    private function invoke(Listing $l, ListingStatus $to, TransitionEvidence $e): void
    {
        $id = $this->revision();
        $p = PropertyAvailability::Eligible;
        match ($to) {
            ListingStatus::Submitted => $l->submit($id, $e, $this->policy, $p),ListingStatus::UnderReview => $l->sendToReview($id, $e, $this->policy, $p),ListingStatus::ChangesRequested => $l->requestChanges($id, $e, $this->policy, $p),ListingStatus::Published => $l->publish($id, ExpirationDate::fromDateTime($e->occurredAt->modify('+100 minutes')), $e, $this->policy, $p),ListingStatus::Suspended => $l->suspend($id, $e, $this->policy, $p),ListingStatus::Expired => $l->expire($id, $e, $this->policy, $p),ListingStatus::Withdrawn => $l->withdraw($id, $e, $this->policy, $p),ListingStatus::Rejected => $l->reject($id, $e, $this->policy, $p),ListingStatus::Archived => $l->archive($id, $e, $this->policy, $p),ListingStatus::Draft => throw new \LogicException
        };
    }

    private function evidenceFor(ListingStatus $from, ListingStatus $to, int $minute): TransitionEvidence
    {
        [$trigger,$origin] = match ($from->value.'>'.$to->value) {
            'draft>submitted' => [TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser],'draft>withdrawn' => [TransitionTrigger::VoluntaryWithdrawal, TransitionOrigin::Advertiser],'draft>archived' => [TransitionTrigger::RetentionDeadlineReached, TransitionOrigin::System],
            'submitted>under_review' => [TransitionTrigger::ReviewStarted, TransitionOrigin::Moderation],'submitted>withdrawn','under_review>withdrawn' => [TransitionTrigger::CancellationAccepted, TransitionOrigin::Moderation],
            'under_review>published' => [TransitionTrigger::FavorableReview, TransitionOrigin::Moderation],'under_review>changes_requested','suspended>changes_requested' => [TransitionTrigger::CorrectableIssues, TransitionOrigin::Moderation],
            'under_review>rejected','suspended>rejected' => [TransitionTrigger::NonRegularizableContent, TransitionOrigin::Moderation],'changes_requested>submitted' => [TransitionTrigger::CorrectionsCompleted, TransitionOrigin::Advertiser],
            'changes_requested>withdrawn','published>withdrawn','expired>withdrawn' => [TransitionTrigger::VoluntaryWithdrawal, TransitionOrigin::Advertiser],'changes_requested>archived' => [TransitionTrigger::RetentionDeadlineReached, TransitionOrigin::System],
            'published>under_review' => [TransitionTrigger::MaterialChange, TransitionOrigin::Moderation],'published>suspended' => [TransitionTrigger::RiskDetected, TransitionOrigin::Moderation],'published>expired' => [TransitionTrigger::PublicationDeadlineReached, TransitionOrigin::System],
            'suspended>published' => [TransitionTrigger::RegularizationValidated, TransitionOrigin::Moderation],'suspended>archived' => [TransitionTrigger::FinalClosureConfirmed, TransitionOrigin::Administration],
            'expired>under_review' => [TransitionTrigger::RenewalReviewRequired, TransitionOrigin::Moderation],'expired>published' => [TransitionTrigger::DirectRenewalApproved, TransitionOrigin::Advertiser],
            'expired>archived','withdrawn>archived' => [TransitionTrigger::ReactivationDeadlineReached, TransitionOrigin::System],'withdrawn>under_review' => [TransitionTrigger::RepublicationApproved, TransitionOrigin::Moderation],
            'rejected>archived' => [TransitionTrigger::AppealDeadlineReached, TransitionOrigin::Moderation],default => [TransitionTrigger::SubmissionConfirmed, TransitionOrigin::Advertiser]
        };

        return $this->evidence($trigger, $origin, $minute);
    }

    private function evidence(TransitionTrigger $trigger, TransitionOrigin $origin, int $minute, string $reason = 'Motif métier explicite'): TransitionEvidence
    {
        return new TransitionEvidence(ActorId::fromString('actor-1'), $trigger, TransitionReason::fromString($reason), $origin, $this->at($minute));
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('70000000-0000-4000-8000-000000000001');
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('50000000-0000-4000-8000-000000000001');
    }

    private function revision(): ListingRevisionId
    {
        return ListingRevisionId::fromString(sprintf('70000000-0000-4000-8000-%012x', $this->sequence++));
    }

    private function at(int $m): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00')->modify("+{$m} minutes");
    }
}
