<?php

namespace App\Application\ModerationOrchestration\Contract;

enum ModerationCommandStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case ForbiddenActor = 'forbidden_actor';
    case FourEyesViolation = 'four_eyes_violation';
    case CaseMissing = 'case_missing';
    case ReportMissing = 'report_missing';
    case ReportNotAccepted = 'report_not_accepted';
    case FindingMissing = 'finding_missing';
    case InsufficientEvidence = 'insufficient_evidence';
    case InvalidSupersession = 'invalid_supersession';
    case NotClosable = 'not_closable';
    case Closed = 'closed';
    case ItemMissing = 'item_missing';
    case LeaseConflict = 'lease_conflict';
    case DependencyUnavailable = 'dependency_unavailable';
    case Rejected = 'rejected';
}
