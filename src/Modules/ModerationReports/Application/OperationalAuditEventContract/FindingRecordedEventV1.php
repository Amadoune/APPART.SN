<?php

namespace Appart\Modules\ModerationReports\Application\OperationalAuditEventContract;

use DateTimeImmutable;

final readonly class FindingRecordedEventV1 extends AbstractModerationOperationalAuditEventV1
{
    public function __construct(
        string $caseId,
        string $findingId,
        int $aggregateVersion,
        string $policyVersion,
        DateTimeImmutable $occurredAt,
        DateTimeImmutable $recordedAt,
        string $correlationId,
        string $causationId,
    ) {
        ModerationOperationalAuditEventIdentityV1::assertUuid($findingId, 'findingId');
        parent::__construct(
            ModerationOperationalAuditEventTypeV1::FindingRecorded,
            $caseId,
            $aggregateVersion,
            ['findingId' => strtolower($findingId)],
            $policyVersion,
            $occurredAt,
            $recordedAt,
            $correlationId,
            $causationId,
        );
    }
}
