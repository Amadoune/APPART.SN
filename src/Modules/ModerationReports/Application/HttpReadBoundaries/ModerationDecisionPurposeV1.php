<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

enum ModerationDecisionPurposeV1: string
{
    case Decide = 'decide';
    case Audit = 'audit';
}
