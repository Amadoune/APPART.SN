<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

final readonly class ModerationDecisionPersistenceReadResult
{
    private function __construct(
        public ModerationPersistenceReadStatus $status,
        public ?ModerationPersistenceRecord $decision,
    ) {}

    public static function found(ModerationPersistenceRecord $decision): self
    {
        return new self(ModerationPersistenceReadStatus::Found, $decision);
    }

    public static function missing(): self
    {
        return new self(ModerationPersistenceReadStatus::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(ModerationPersistenceReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ModerationPersistenceReadStatus::DependencyUnavailable, null);
    }
}
