<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value;

use DateTimeImmutable;
use DateTimeZone;

final readonly class LeadIngressAntiAbuseObservedAt
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
