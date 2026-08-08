<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead;

final readonly class LeadIngressAntiAbuseRuntimeReadResult
{
    private function __construct(public LeadIngressAntiAbuseRuntimeReadStatus $status) {}

    public static function allowed(): self
    {
        return new self(LeadIngressAntiAbuseRuntimeReadStatus::Allowed);
    }

    public static function blocked(): self
    {
        return new self(LeadIngressAntiAbuseRuntimeReadStatus::Blocked);
    }

    public static function missing(): self
    {
        return new self(LeadIngressAntiAbuseRuntimeReadStatus::Missing);
    }

    public static function corrupted(): self
    {
        return new self(LeadIngressAntiAbuseRuntimeReadStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(LeadIngressAntiAbuseRuntimeReadStatus::DependencyUnavailable);
    }
}
