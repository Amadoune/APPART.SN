<?php

namespace Appart\Modules\AdministrationAudit\Domain\Model;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use DateTimeImmutable;

final readonly class AuditEntry
{
    private function __construct(public int $sequence, public string $fact, public ActorId $actorId, public AuditReason $reason, public DateTimeImmutable $recordedAt) {}

    public static function record(int $sequence, string $fact, ActorId $actorId, AuditReason $reason, DateTimeImmutable $recordedAt): self
    {
        if ($sequence < 1 || preg_match('/^[a-z][a-z0-9_]{2,63}$/', $fact) !== 1) {
            throw InvalidAuditValue::field('audit_entry');
        }

        return new self($sequence, $fact, $actorId, $reason, $recordedAt);
    }
}
