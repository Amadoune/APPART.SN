<?php

namespace App\Application\ModerationResidualOperationalAuditContract;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventTypeV1;
use InvalidArgumentException;

final class ResidualOperationalAuditConversionMatrixV1
{
    public function conversion(string $eventType): ResidualOperationalAuditConversionV1
    {
        return match ($eventType) {
            ModerationEventTypeV1::ReportSubmitted->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::ReportSubmitted,
                'reportId',
            ),
            ModerationEventTypeV1::ReportValidated->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::ReportValidated,
                'reportId',
            ),
            ModerationOperationalAuditEventTypeV1::FindingRecorded->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::FindingRecorded,
                'findingId',
            ),
            ModerationOperationalAuditEventTypeV1::QueueItemClaimed->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::QueueItemClaimed,
                'queueItemId',
            ),
            ModerationEventTypeV1::DecisionIssued->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::DecisionIssued,
                'decisionId',
            ),
            ModerationEventTypeV1::CaseClosed->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::CaseClosed,
                'currentDecisionId',
            ),
            ModerationEventTypeV1::TargetActionCompleted->value => new ResidualOperationalAuditConversionV1(
                AdministrationAuditOperationV1::ListingHandoffCompleted,
                'decisionId',
            ),
            default => throw new InvalidArgumentException('Unsupported operational Audit Event V1.'),
        };
    }
}
