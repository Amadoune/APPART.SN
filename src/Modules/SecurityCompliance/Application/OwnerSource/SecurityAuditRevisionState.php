<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class SecurityAuditRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(SecurityComplianceSubjectKey|string $subject, public int $revision, public SecurityAuditStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || $decision !== SecurityAuditStatusV1::Available) {
            throw new InvalidArgumentException('Security Compliance security audit revision is invalid.');
        } $this->subjectKey = ($subject instanceof SecurityComplianceSubjectKey ? $subject : new SecurityComplianceSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Security Compliance security audit chronology is invalid.');
        }
    }
}
