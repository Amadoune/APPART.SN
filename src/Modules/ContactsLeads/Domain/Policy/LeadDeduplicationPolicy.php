<?php

namespace Appart\Modules\ContactsLeads\Domain\Policy;

use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;

final readonly class LeadDeduplicationPolicy
{
    public const WINDOW_SECONDS = 900;

    public function conflicts(LeadTimestamp $existing, LeadTimestamp $candidate): bool
    {
        return abs($candidate->value->getTimestamp() - $existing->value->getTimestamp()) <= self::WINDOW_SECONDS;
    }
}
