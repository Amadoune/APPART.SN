<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum ReportCompleteness: string
{
    case Complete = 'complete';
    case Incomplete = 'incomplete';
}
