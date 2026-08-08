<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerSource;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;

final readonly class SecretInventoryReadResult
{
    private function __construct(public SecretInventoryStatusV1 $status, public ?SecretInventoryRevisionState $revision) {}

    public static function found(SecretInventoryRevisionState $revision): self
    {
        return new self($revision->decision, $revision);
    }

    public static function missing(): self
    {
        return new self(SecretInventoryStatusV1::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(SecretInventoryStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(SecretInventoryStatusV1::DependencyUnavailable, null);
    }
}
