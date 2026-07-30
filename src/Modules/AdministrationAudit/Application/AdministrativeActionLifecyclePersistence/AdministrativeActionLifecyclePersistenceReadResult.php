<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionLifecyclePersistenceReadResult
{
    private function __construct(
        public AdministrativeActionId $actionId,
        public AdministrativeActionLifecyclePersistenceReadStatus $status,
        public ?AdministrativeActionLifecycleStoredState $snapshot,
    ) {}

    public static function found(AdministrativeActionLifecycleStoredState $snapshot): self
    {
        return new self($snapshot->actionId, AdministrativeActionLifecyclePersistenceReadStatus::Found, $snapshot);
    }

    public static function notEnrolled(AdministrativeActionId $actionId): self
    {
        return new self($actionId, AdministrativeActionLifecyclePersistenceReadStatus::NotEnrolled, null);
    }

    public static function corrupted(AdministrativeActionId $actionId): self
    {
        return new self($actionId, AdministrativeActionLifecyclePersistenceReadStatus::Corrupted, null);
    }
}
