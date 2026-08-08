<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;

final readonly class PrivacyPolicyReadResult
{
    private function __construct(public PrivacyPolicyStatusV1 $status, public ?PrivacyPolicyRevisionState $revision) {}

    public static function found(PrivacyPolicyRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(PrivacyPolicyStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(PrivacyPolicyStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(PrivacyPolicyStatusV1::DependencyUnavailable, null);
    }
}
