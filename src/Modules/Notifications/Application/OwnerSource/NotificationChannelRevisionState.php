<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class NotificationChannelRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(NotificationSubjectKey|string $subject, public int $revision, public NotificationChannelStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, [NotificationChannelStatusV1::Allowed, NotificationChannelStatusV1::Blocked], true)) {
            throw new InvalidArgumentException('Notification channel revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof NotificationSubjectKey ? $subject : new NotificationSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Notification channel chronology is invalid.');
        }
    }
}
