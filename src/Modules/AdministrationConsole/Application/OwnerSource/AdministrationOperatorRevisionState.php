<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerSource;

use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class AdministrationOperatorRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(AdministrationSubjectKey|string $subject, public int $revision, public AdministrationOperatorStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, [AdministrationOperatorStatusV1::Available, AdministrationOperatorStatusV1::Unavailable], true)) {
            throw new InvalidArgumentException('Administration operator revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof AdministrationSubjectKey ? $subject : new AdministrationSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Administration operator chronology is invalid.');
        }
    }
}
