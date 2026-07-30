<?php

namespace Appart\Modules\ModerationReports\Application\OperationalAuditEventContract;

use DateTimeImmutable;

final readonly class QueueItemClaimedEventV1 extends AbstractModerationOperationalAuditEventV1
{
    public function __construct(
        string $caseId,
        string $queueItemId,
        int $aggregateVersion,
        string $policyVersion,
        DateTimeImmutable $occurredAt,
        DateTimeImmutable $recordedAt,
        string $correlationId,
        string $causationId,
    ) {
        ModerationOperationalAuditEventIdentityV1::assertUuid($queueItemId, 'queueItemId');
        parent::__construct(
            ModerationOperationalAuditEventTypeV1::QueueItemClaimed,
            $caseId,
            $aggregateVersion,
            ['queueItemId' => strtolower($queueItemId)],
            $policyVersion,
            $occurredAt,
            $recordedAt,
            $correlationId,
            $causationId,
        );
    }
}
