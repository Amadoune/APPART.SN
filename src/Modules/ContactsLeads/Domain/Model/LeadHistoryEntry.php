<?php

namespace Appart\Modules\ContactsLeads\Domain\Model;

use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadStatus;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;

final readonly class LeadHistoryEntry
{
    public function __construct(public LeadStatus $status, public LeadTimestamp $at, public ?string $reason = null) {}
}
