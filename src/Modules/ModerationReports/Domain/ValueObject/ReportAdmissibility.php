<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum ReportAdmissibility: string
{
    case Admissible = 'admissible';
    case Inadmissible = 'inadmissible';
}
