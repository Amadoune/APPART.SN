<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionContextualInspectionResult
{
    private function __construct(
        public AdministrativeActionId $actionId,
        public AdministrativeActionContextualInspectionStatus $status,
        public ?AdministrativeActionContextualAppendInspection $snapshot,
    ) {}

    public static function found(AdministrativeActionContextualAppendInspection $snapshot): self
    {
        return new self($snapshot->actionId, AdministrativeActionContextualInspectionStatus::Found, $snapshot);
    }

    public static function missing(AdministrativeActionId $actionId): self
    {
        return new self($actionId, AdministrativeActionContextualInspectionStatus::Missing, null);
    }

    public static function corrupted(AdministrativeActionId $actionId): self
    {
        return new self($actionId, AdministrativeActionContextualInspectionStatus::Corrupted, null);
    }
}
