<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSource;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

final readonly class ConsentRevisionReadResult
{
    private function __construct(
        public LeadIngressIntentId $intentId,
        public ConsentRevisionReadStatus $status,
        public ?ConsentRevisionState $revision,
    ) {}

    public static function found(ConsentRevisionState $revision): self
    {
        return new self($revision->intentId, ConsentRevisionReadStatus::Found, $revision);
    }

    public static function missing(LeadIngressIntentId $intentId): self
    {
        return new self($intentId, ConsentRevisionReadStatus::Missing, null);
    }

    public static function corrupted(LeadIngressIntentId $intentId): self
    {
        return new self($intentId, ConsentRevisionReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(LeadIngressIntentId $intentId): self
    {
        return new self($intentId, ConsentRevisionReadStatus::DependencyUnavailable, null);
    }
}
