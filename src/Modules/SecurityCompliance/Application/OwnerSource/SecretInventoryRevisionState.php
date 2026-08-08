<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class SecretInventoryRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(SecurityComplianceSubjectKey|string $subject, public int $revision, public SecretInventoryStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || $decision !== SecretInventoryStatusV1::Available) {
            throw new InvalidArgumentException('Security Compliance secret inventory revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof SecurityComplianceSubjectKey ? $subject : new SecurityComplianceSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Security Compliance secret inventory chronology is invalid.');
        }
    }
}
