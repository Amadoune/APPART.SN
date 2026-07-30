<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;

final readonly class EligibilityRevision
{
    public function __construct(public string $coherenceId, public int $version, public LeadTimestamp $effectiveAt)
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $coherenceId) !== 1 || $version < 1) {
            throw InvalidLeadValue::field('eligibility_revision');
        }
    }

    public function equals(self $other): bool
    {
        return mb_strtolower($this->coherenceId) === mb_strtolower($other->coherenceId)
            && $this->version === $other->version
            && $this->effectiveAt->equals($other->effectiveAt);
    }
}
