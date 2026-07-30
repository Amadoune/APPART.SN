<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

final readonly class ModerationCasePersistenceReadResult
{
    private function __construct(
        public ModerationPersistenceReadStatus $status,
        public ?ModerationCasePersistenceState $state,
    ) {}

    public static function found(ModerationCasePersistenceState $state): self
    {
        return new self(ModerationPersistenceReadStatus::Found, $state);
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
