<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class LegacyMigrationWaveRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(LegacyMigrationSubjectKey|string $subject, public int $revision, public LegacyMigrationWaveStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, [LegacyMigrationWaveStatusV1::Ready, LegacyMigrationWaveStatusV1::Blocked, LegacyMigrationWaveStatusV1::Completed], true)) {
            throw new InvalidArgumentException('Legacy Migration wave revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof LegacyMigrationSubjectKey ? $subject : new LegacyMigrationSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Legacy Migration wave chronology is invalid.');
        }
    }
}
