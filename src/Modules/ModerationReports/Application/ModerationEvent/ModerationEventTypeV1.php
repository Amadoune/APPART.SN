<?php

namespace Appart\Modules\ModerationReports\Application\ModerationEvent;

enum ModerationEventTypeV1: string
{
    case ReportSubmitted = 'moderation.report.submitted.v1';
    case ReportValidated = 'moderation.report.validated.v1';
    case DecisionIssued = 'moderation.decision.issued.v1';
    case CaseClosed = 'moderation.case.closed.v1';
    case TargetActionCompleted = 'moderation.target-action.completed.v1';
}
