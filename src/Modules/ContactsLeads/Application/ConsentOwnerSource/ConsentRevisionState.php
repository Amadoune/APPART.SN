<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSource;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class ConsentRevisionState
{
    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(
        public LeadIngressIntentId $intentId,
        public int $revision,
        public ConsentRevisionDecision $decision,
        DateTimeImmutable $effectiveAt,
        DateTimeImmutable $recordedAt,
        public ?string $policyReference = null,
    ) {
        if ($revision < 1) {
            throw new InvalidArgumentException('Consent revision must be positive.');
        }

        $this->effectiveAt = $effectiveAt->setTimezone(new DateTimeZone('UTC'));
        $this->recordedAt = $recordedAt->setTimezone(new DateTimeZone('UTC'));
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Consent revision cannot be recorded before it takes effect.');
        }
        if ($policyReference !== null && preg_match('/^[a-z0-9][a-z0-9._:-]{0,63}$/', $policyReference) !== 1) {
            throw new InvalidArgumentException('Invalid consent policy reference.');
        }
    }
}
