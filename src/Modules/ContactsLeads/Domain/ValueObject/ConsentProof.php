<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;

final readonly class ConsentProof
{
    private function __construct(public ConsentDecision $decision, public ConsentPurpose $purpose, public string $textVersion, public LeadTimestamp $acceptedAt) {}

    public static function granted(ConsentPurpose $purpose, string $textVersion, LeadTimestamp $acceptedAt): self
    {
        $textVersion = trim($textVersion);
        if (preg_match('/^[a-z0-9][a-z0-9._-]{0,49}$/i', $textVersion) !== 1) {
            throw InvalidLeadValue::field('consent_text_version');
        }

        return new self(ConsentDecision::Granted, $purpose, mb_strtolower($textVersion), $acceptedAt);
    }

    public static function denied(ConsentPurpose $purpose, string $textVersion, LeadTimestamp $at): self
    {
        $proof = self::granted($purpose, $textVersion, $at);

        return new self(ConsentDecision::Denied, $proof->purpose, $proof->textVersion, $proof->acceptedAt);
    }
}
