<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

enum AdministrativeActionHistoricalMirrorMutationKind: string
{
    case Record = 'record';
    case Approve = 'approve';
    case Reject = 'reject';
}
