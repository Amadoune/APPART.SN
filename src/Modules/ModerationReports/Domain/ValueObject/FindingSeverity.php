<?php

namespace Appart\Modules\ModerationReports\Domain\ValueObject;

enum FindingSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';
}
