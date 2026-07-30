<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

enum AdministrativeActionRecordingDisposition: string
{
    case DirectRecording = 'direct_recording';
    case IndependentApprovalRequired = 'independent_approval_required';
}
