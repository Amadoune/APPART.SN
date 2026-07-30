<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend;

enum AdministrationAuditOperationV1: string
{
    case ReportSubmitted = 'report_submitted';
    case ReportValidated = 'report_validated';
    case FindingRecorded = 'finding_recorded';
    case DecisionIssued = 'decision_issued';
    case CaseClosed = 'case_closed';
    case QueueItemClaimed = 'queue_item_claimed';
    case ListingHandoffCompleted = 'listing_handoff_completed';
}
