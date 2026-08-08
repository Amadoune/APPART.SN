<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

final readonly class ExperienceAcceptanceOutboxPolicy
{
    public const SAVEPOINT = 'experience_acceptance_outbox';

    public const MAX_ATTEMPTS = 10;

    public const MAX_READ_LIMIT = 100;

    public function acceptsAttempts(int $attempts): bool
    {
        return $attempts >= 0 && $attempts <= self::MAX_ATTEMPTS;
    }

    public function acceptsReadLimit(int $limit): bool
    {
        return $limit >= 1 && $limit <= self::MAX_READ_LIMIT;
    }
}
