<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

enum TransitionTrigger: string
{
    case DraftStarted = 'draft_started';
    case SubmissionConfirmed = 'submission_confirmed';
    case ReviewStarted = 'review_started';
    case FavorableReview = 'favorable_review';
    case CorrectableIssues = 'correctable_issues';
    case CorrectionsCompleted = 'corrections_completed';
    case NonRegularizableContent = 'non_regularizable_content';
    case VoluntaryWithdrawal = 'voluntary_withdrawal';
    case CancellationAccepted = 'cancellation_accepted';
    case MaterialChange = 'material_change';
    case RiskDetected = 'risk_detected';
    case RegularizationValidated = 'regularization_validated';
    case PublicationDeadlineReached = 'publication_deadline_reached';
    case DirectRenewalApproved = 'direct_renewal_approved';
    case RenewalReviewRequired = 'renewal_review_required';
    case RepublicationApproved = 'republication_approved';
    case RetentionDeadlineReached = 'retention_deadline_reached';
    case ReactivationDeadlineReached = 'reactivation_deadline_reached';
    case AppealDeadlineReached = 'appeal_deadline_reached';
    case FinalClosureConfirmed = 'final_closure_confirmed';
}
