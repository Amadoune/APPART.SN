<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead;

final readonly class ConsentOwnerSourceRuntimeReadResult
{
    private function __construct(public ConsentOwnerSourceRuntimeReadStatus $status) {}

    public static function granted(): self
    {
        return new self(ConsentOwnerSourceRuntimeReadStatus::Granted);
    }

    public static function denied(): self
    {
        return new self(ConsentOwnerSourceRuntimeReadStatus::Denied);
    }

    public static function missing(): self
    {
        return new self(ConsentOwnerSourceRuntimeReadStatus::Missing);
    }

    public static function corrupted(): self
    {
        return new self(ConsentOwnerSourceRuntimeReadStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ConsentOwnerSourceRuntimeReadStatus::DependencyUnavailable);
    }
}
