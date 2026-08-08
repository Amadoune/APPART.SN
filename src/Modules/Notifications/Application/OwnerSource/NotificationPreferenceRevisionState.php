<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class NotificationPreferenceRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(NotificationSubjectKey|string $subject, public int $revision, public NotificationPreferenceStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, [NotificationPreferenceStatusV1::Enabled, NotificationPreferenceStatusV1::Disabled], true)) {
            throw new InvalidArgumentException('Notification preference revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof NotificationSubjectKey ? $subject : new NotificationSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Notification preference chronology is invalid.');
        }
    }
}
