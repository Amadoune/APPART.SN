<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

enum ModerationCaseViewLevelV1: string
{
    case Investigate = 'investigate';
    case Audit = 'audit';
}
