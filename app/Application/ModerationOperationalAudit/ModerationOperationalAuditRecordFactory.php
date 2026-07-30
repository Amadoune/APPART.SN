<?php

namespace App\Application\ModerationOperationalAudit;

use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use App\Application\ModerationResidualOperationalAuditContract\ResidualOperationalAuditConversionMatrixV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOutcomeV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditSourceOwnerV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventTypeV1;

final class ModerationOperationalAuditRecordFactory
{
    public function __construct(
        private ResidualOperationalAuditConversionMatrixV1 $conversions = new ResidualOperationalAuditConversionMatrixV1,
    ) {}

    public function map(
        ModerationOperationalAuditOutboxMessageV1|ModerationEventV1 $message,
    ): ?AdministrationAuditRecordV1 {
        $event = $message instanceof ModerationOperationalAuditOutboxMessageV1
            ? $message->event
            : $message;
        if ($event instanceof ModerationEventV1) {
            $conversion = $this->conversions->conversion($event->type->value);
            $subjectId = $event->payload[$conversion->subjectKey] ?? null;
            if (! is_string($subjectId)) {
                return null;
            }

            return $this->record(
                $conversion->operation,
                $subjectId,
                $event->caseId,
                $event->correlationId,
                $event->causationId,
                $event->occurredAt,
                $event->policyVersion,
            );
        }
        [$operation, $payloadKey] = match ($event->eventType()) {
            ModerationOperationalAuditEventTypeV1::FindingRecorded => [
                AdministrationAuditOperationV1::FindingRecorded,
                'findingId',
            ],
            ModerationOperationalAuditEventTypeV1::QueueItemClaimed => [
                AdministrationAuditOperationV1::QueueItemClaimed,
                'queueItemId',
            ],
        };
        $subjectId = $event->payload()[$payloadKey] ?? null;
        if (! is_string($subjectId)) {
            return null;
        }

        return $this->record(
            $operation,
            $subjectId,
            $event->caseId(),
            $event->correlationId(),
            $event->causationId(),
            $event->occurredAt(),
            $event->policyVersion(),
        );
    }

    private function record(
        AdministrationAuditOperationV1 $operation,
        string $subjectId,
        string $caseId,
        string $correlationId,
        string $causationId,
        \DateTimeImmutable $occurredAt,
        string $policyVersion,
    ): AdministrationAuditRecordV1 {
        return AdministrationAuditRecordV1::create(
            AdministrationAuditSourceOwnerV1::ModerationReports,
            $operation,
            $subjectId,
            $caseId,
            AdministrationAuditOutcomeV1::Applied,
            $correlationId,
            $causationId,
            $occurredAt,
            $policyVersion,
        );
    }
}
