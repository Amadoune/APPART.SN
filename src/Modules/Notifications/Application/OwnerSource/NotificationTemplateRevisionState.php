<?php

namespace Appart\Modules\Notifications\Application\OwnerSource;

use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class NotificationTemplateRevisionState
{
    public string $subjectKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(NotificationSubjectKey|string $subject, public int $revision, public NotificationTemplateStatusV1 $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || $decision !== NotificationTemplateStatusV1::Available) {
            throw new InvalidArgumentException('Notification template revision is invalid.');
        }
        $this->subjectKey = ($subject instanceof NotificationSubjectKey ? $subject : new NotificationSubjectKey($subject))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Notification template chronology is invalid.');
        }
    }
}
