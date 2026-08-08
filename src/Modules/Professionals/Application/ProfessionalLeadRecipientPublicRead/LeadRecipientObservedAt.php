<?php

namespace Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead;

use DateTimeImmutable;
use DateTimeZone;

final readonly class LeadRecipientObservedAt
{
    public DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value->setTimezone(new DateTimeZone('UTC'));
    }

    public function canonical(): string
    {
        return $this->value->format('Y-m-d\TH:i:s.u\Z');
    }
}
