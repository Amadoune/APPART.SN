<?php

namespace App\Application\ModerationResidualOperationalAuditContract;

use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventTypeV1;

final class ResidualOperationalAuditRuntimeCatalogV1
{
    /** @return list<string> */
    public function types(): array
    {
        return [
            ModerationEventTypeV1::ReportSubmitted->value,
            ModerationEventTypeV1::ReportValidated->value,
            ModerationOperationalAuditEventTypeV1::FindingRecorded->value,
            ModerationOperationalAuditEventTypeV1::QueueItemClaimed->value,
            ModerationEventTypeV1::DecisionIssued->value,
            ModerationEventTypeV1::CaseClosed->value,
            ModerationEventTypeV1::TargetActionCompleted->value,
        ];
    }

    public function accepts(string $eventType): bool
    {
        return in_array($eventType, $this->types(), true);
    }
}
