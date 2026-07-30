<?php

namespace Appart\Modules\ListingLifecycle\Domain\Policy;

use Appart\Modules\ListingLifecycle\Domain\Exception\ListingViolation;
use Appart\Modules\ListingLifecycle\Domain\Exception\TransitionConditionNotSatisfied;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationMediaAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\RenewalRoute;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;

final readonly class ListingTransitionPolicy
{
    public function assertPublicationMedia(PublicationMediaAvailability $media): void
    {
        if ($media !== PublicationMediaAvailability::Eligible) {
            throw new TransitionConditionNotSatisfied("Publication media are {$media->value}.");
        }
    }

    public function assertDraftCreation(TransitionEvidence $evidence, PropertyAvailability $property): void
    {
        if ($evidence->trigger !== TransitionTrigger::DraftStarted || $evidence->origin !== TransitionOrigin::Advertiser || $property !== PropertyAvailability::Eligible) {
            throw new TransitionConditionNotSatisfied('Draft creation conditions are not satisfied.');
        }
    }

    public function assertAllowed(ListingStatus $from, ListingStatus $to, TransitionEvidence $evidence, PropertyAvailability $property): void
    {
        $triggers = self::allowedTriggers($from, $to);
        if ($triggers === []) {
            throw ListingViolation::transition($from, $to);
        }
        if (! in_array($evidence->trigger, $triggers, true) || ! in_array($evidence->origin, self::allowedOrigins($evidence->trigger), true)) {
            throw new TransitionConditionNotSatisfied('Transition evidence does not satisfy the business policy.');
        }
        if (in_array($to, [ListingStatus::Submitted, ListingStatus::UnderReview, ListingStatus::Published], true) && $property !== PropertyAvailability::Eligible) {
            throw new TransitionConditionNotSatisfied("Property is {$property->value}.");
        }
        if ($from === ListingStatus::Expired && in_array($to, [ListingStatus::Published, ListingStatus::UnderReview], true)) {
            $expected = $to === ListingStatus::Published ? RenewalRoute::DirectPublication : RenewalRoute::NewReview;
            if ($this->renewalRoute($evidence, $property) !== $expected) {
                throw new TransitionConditionNotSatisfied('The selected renewal route is not authorized.');
            }
        }
    }

    public function renewalRoute(TransitionEvidence $evidence, PropertyAvailability $property): RenewalRoute
    {
        if ($property !== PropertyAvailability::Eligible) {
            return RenewalRoute::Prohibited;
        }

        return match ($evidence->trigger) {
            TransitionTrigger::DirectRenewalApproved => RenewalRoute::DirectPublication,
            TransitionTrigger::RenewalReviewRequired => RenewalRoute::NewReview,
            default => RenewalRoute::Prohibited,
        };
    }

    /** @return list<TransitionTrigger> */
    private static function allowedTriggers(ListingStatus $from, ListingStatus $to): array
    {
        return match ($from->value.'>'.$to->value) {
            'draft>submitted' => [TransitionTrigger::SubmissionConfirmed],
            'draft>withdrawn' => [TransitionTrigger::VoluntaryWithdrawal],
            'draft>archived' => [TransitionTrigger::RetentionDeadlineReached],
            'submitted>under_review' => [TransitionTrigger::ReviewStarted],
            'submitted>withdrawn' => [TransitionTrigger::CancellationAccepted],
            'under_review>published' => [TransitionTrigger::FavorableReview],
            'under_review>changes_requested' => [TransitionTrigger::CorrectableIssues],
            'under_review>rejected' => [TransitionTrigger::NonRegularizableContent],
            'under_review>withdrawn' => [TransitionTrigger::CancellationAccepted],
            'changes_requested>submitted' => [TransitionTrigger::CorrectionsCompleted],
            'changes_requested>withdrawn' => [TransitionTrigger::VoluntaryWithdrawal],
            'changes_requested>archived' => [TransitionTrigger::RetentionDeadlineReached],
            'published>under_review' => [TransitionTrigger::MaterialChange],
            'published>suspended' => [TransitionTrigger::RiskDetected],
            'published>expired' => [TransitionTrigger::PublicationDeadlineReached],
            'published>withdrawn' => [TransitionTrigger::VoluntaryWithdrawal],
            'suspended>published' => [TransitionTrigger::RegularizationValidated],
            'suspended>changes_requested' => [TransitionTrigger::CorrectableIssues],
            'suspended>rejected' => [TransitionTrigger::NonRegularizableContent],
            'suspended>archived' => [TransitionTrigger::FinalClosureConfirmed],
            'expired>under_review' => [TransitionTrigger::RenewalReviewRequired],
            'expired>published' => [TransitionTrigger::DirectRenewalApproved],
            'expired>withdrawn' => [TransitionTrigger::VoluntaryWithdrawal],
            'expired>archived' => [TransitionTrigger::ReactivationDeadlineReached, TransitionTrigger::FinalClosureConfirmed],
            'withdrawn>under_review' => [TransitionTrigger::RepublicationApproved],
            'withdrawn>archived' => [TransitionTrigger::ReactivationDeadlineReached, TransitionTrigger::FinalClosureConfirmed],
            'rejected>archived' => [TransitionTrigger::AppealDeadlineReached, TransitionTrigger::FinalClosureConfirmed],
            default => [],
        };
    }

    /** @return list<TransitionOrigin> */
    private static function allowedOrigins(TransitionTrigger $trigger): array
    {
        return match ($trigger) {
            TransitionTrigger::DraftStarted,
            TransitionTrigger::SubmissionConfirmed,
            TransitionTrigger::CorrectionsCompleted,
            TransitionTrigger::VoluntaryWithdrawal,
            TransitionTrigger::DirectRenewalApproved => [TransitionOrigin::Advertiser],
            TransitionTrigger::RetentionDeadlineReached,
            TransitionTrigger::ReactivationDeadlineReached,
            TransitionTrigger::PublicationDeadlineReached => [TransitionOrigin::System],
            TransitionTrigger::RiskDetected => [TransitionOrigin::Moderation, TransitionOrigin::System, TransitionOrigin::Administration],
            TransitionTrigger::FinalClosureConfirmed => [TransitionOrigin::Moderation, TransitionOrigin::Administration],
            default => [TransitionOrigin::Moderation, TransitionOrigin::Administration],
        };
    }
}
