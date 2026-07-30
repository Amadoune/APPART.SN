<?php

namespace Appart\Modules\ModerationReports\Application\OperationalAuditEventContract;

use DateTimeImmutable;
use InvalidArgumentException;

abstract readonly class AbstractModerationOperationalAuditEventV1 implements ModerationOperationalAuditEventV1
{
    private string $eventId;

    private string $checksum;

    /** @param array<string, int|string> $payloadValue */
    protected function __construct(
        private ModerationOperationalAuditEventTypeV1 $type,
        private string $caseIdValue,
        private int $aggregateVersionValue,
        private array $payloadValue,
        private string $policyVersionValue,
        private DateTimeImmutable $occurredAtValue,
        private DateTimeImmutable $recordedAtValue,
        private string $correlationIdValue,
        private string $causationIdValue,
    ) {
        ModerationOperationalAuditEventIdentityV1::assertUuid($caseIdValue, 'caseId');
        ModerationOperationalAuditEventIdentityV1::assertUuid($correlationIdValue, 'correlationId');
        ModerationOperationalAuditEventIdentityV1::assertUuid($causationIdValue, 'causationId');
        if ($aggregateVersionValue < 1) {
            throw new InvalidArgumentException('aggregateVersion must be positive.');
        }
        if (preg_match('/^[A-Za-z0-9._-]{1,64}$/', $policyVersionValue) !== 1) {
            throw new InvalidArgumentException('policyVersion must use the closed token format.');
        }

        $this->eventId = ModerationOperationalAuditEventIdentityV1::eventId(
            $type,
            $caseIdValue,
            $aggregateVersionValue,
            $causationIdValue,
        );
        $this->checksum = ModerationOperationalAuditEventIdentityV1::checksum(
            $type,
            $caseIdValue,
            $aggregateVersionValue,
            $payloadValue,
            $policyVersionValue,
            $occurredAtValue,
            $recordedAtValue,
            $correlationIdValue,
            $causationIdValue,
        );
    }

    final public function eventId(): string
    {
        return $this->eventId;
    }

    final public function eventType(): ModerationOperationalAuditEventTypeV1
    {
        return $this->type;
    }

    final public function caseId(): string
    {
        return strtolower($this->caseIdValue);
    }

    final public function aggregateVersion(): int
    {
        return $this->aggregateVersionValue;
    }

    /** @return array<string, int|string> */
    final public function payload(): array
    {
        $payload = $this->payloadValue;
        ksort($payload, SORT_STRING);

        return $payload;
    }

    final public function policyVersion(): string
    {
        return $this->policyVersionValue;
    }

    final public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAtValue;
    }

    final public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAtValue;
    }

    final public function correlationId(): string
    {
        return strtolower($this->correlationIdValue);
    }

    final public function causationId(): string
    {
        return strtolower($this->causationIdValue);
    }

    final public function checksum(): string
    {
        return $this->checksum;
    }
}
