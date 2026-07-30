<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum ReportHandlingDecision: string
{
    case TakeCharge = 'take_charge';
    case Dismiss = 'dismiss';
}
