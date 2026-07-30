<?php

namespace App\Application\ModerationOperationalAuditEventProduction;

use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventV1;
use DateTimeImmutable;

final readonly class ModerationOperationalAuditOutboxMessageV1
{
    public string $messageId;

    /** @param ModerationEventV1|ModerationOperationalAuditEventV1 $event */
    public function __construct(public object $event)
    {
        $this->messageId = 'moderation-v1-'.hash(
            'sha256',
            $this->eventId()."\n".$this->checksum(),
        );
    }

    public function eventId(): string
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->eventId
            : $this->event->eventId();
    }

    public function eventType(): string
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->type->value
            : $this->event->eventType()->value;
    }

    public function caseId(): string
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->caseId
            : $this->event->caseId();
    }

    public function aggregateVersion(): int
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->aggregateVersion
            : $this->event->aggregateVersion();
    }

    public function checksum(): string
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->checksum
            : $this->event->checksum();
    }

    public function correlationId(): string
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->correlationId
            : $this->event->correlationId();
    }

    public function causationId(): string
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->causationId
            : $this->event->causationId();
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->occurredAt
            : $this->event->occurredAt();
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->event instanceof ModerationEventV1
            ? $this->event->recordedAt
            : $this->event->recordedAt();
    }

    /** @return array<string, mixed> */
    public function fields(): array
    {
        $contract = $this->event instanceof ModerationEventV1
            ? $this->event->contract()
            : [
                'eventId' => $this->event->eventId(),
                'eventType' => $this->event->eventType()->value,
                'caseId' => $this->event->caseId(),
                'aggregateVersion' => $this->event->aggregateVersion(),
                'payload' => $this->event->payload(),
                'policyVersion' => $this->event->policyVersion(),
                'occurredAt' => $this->event->occurredAt()->format('Y-m-d\TH:i:s.uP'),
                'recordedAt' => $this->event->recordedAt()->format('Y-m-d\TH:i:s.uP'),
                'correlationId' => $this->event->correlationId(),
                'causationId' => $this->event->causationId(),
                'checksum' => $this->event->checksum(),
            ];

        return [
            'messageId' => $this->messageId,
            'transportVersion' => 1,
            'payload' => $contract,
            'metadata' => [
                'source' => 'ModerationReports',
                'eventId' => $this->eventId(),
                'checksum' => $this->checksum(),
            ],
        ];
    }
}
