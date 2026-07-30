<?php

namespace Appart\Modules\ModerationReports\Application\OperationalAuditEventContract;

use DateTimeImmutable;

interface ModerationOperationalAuditEventV1
{
    public function eventId(): string;

    public function eventType(): ModerationOperationalAuditEventTypeV1;

    public function caseId(): string;

    public function aggregateVersion(): int;

    /** @return array<string, int|string> */
    public function payload(): array;

    public function policyVersion(): string;

    public function occurredAt(): DateTimeImmutable;

    public function recordedAt(): DateTimeImmutable;

    public function correlationId(): string;

    public function causationId(): string;

    public function checksum(): string;
}
