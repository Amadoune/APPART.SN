<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum ReportAuthenticity: string
{
    case Sufficient = 'sufficient';
    case Insufficient = 'insufficient';
}
