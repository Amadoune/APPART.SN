<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

final readonly class AntiAbuseRevisionReadResult
{
    private function __construct(
        public LeadIngressIntentId $intentId,
        public AntiAbuseRevisionReadStatus $status,
        public ?AntiAbuseRevisionState $revision,
    ) {}

    public static function found(AntiAbuseRevisionState $revision): self
    {
        return new self($revision->intentId, AntiAbuseRevisionReadStatus::Found, $revision);
    }

    public static function missing(LeadIngressIntentId $intentId): self
    {
        return new self($intentId, AntiAbuseRevisionReadStatus::Missing, null);
    }

    public static function corrupted(LeadIngressIntentId $intentId): self
    {
        return new self($intentId, AntiAbuseRevisionReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(LeadIngressIntentId $intentId): self
    {
        return new self($intentId, AntiAbuseRevisionReadStatus::DependencyUnavailable, null);
    }
}
