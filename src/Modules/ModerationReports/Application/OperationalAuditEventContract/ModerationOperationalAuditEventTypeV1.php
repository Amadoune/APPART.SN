<?php

namespace Appart\Modules\ModerationReports\Application\OperationalAuditEventContract;

enum ModerationOperationalAuditEventTypeV1: string
{
    case FindingRecorded = 'moderation.finding.recorded.v1';
    case QueueItemClaimed = 'moderation.queue-item.claimed.v1';
}
