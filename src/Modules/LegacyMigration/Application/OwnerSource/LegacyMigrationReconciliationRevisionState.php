<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class LegacyMigrationReconciliationRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(LegacyMigrationSubjectKey|string $subject, public int $revision, public LegacyMigrationReconciliationStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, [LegacyMigrationReconciliationStatusV1::Matched, LegacyMigrationReconciliationStatusV1::Divergent, LegacyMigrationReconciliationStatusV1::Pending], true)) {
            throw new InvalidArgumentException('Legacy Migration reconciliation revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof LegacyMigrationSubjectKey ? $subject : new LegacyMigrationSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Legacy Migration reconciliation chronology is invalid.');
        }
    }
}
