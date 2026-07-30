<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

enum AdministrativeActionLifecycleAction: string
{
    case Record = 'record';
    case Approve = 'approve';
    case Reject = 'reject';
    case Unknown = 'unknown';
}
