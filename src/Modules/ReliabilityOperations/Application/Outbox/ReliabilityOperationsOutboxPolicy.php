<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

final readonly class ReliabilityOperationsOutboxPolicy
{
    public const SAVEPOINT = 'reliability_operations_outbox';

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
