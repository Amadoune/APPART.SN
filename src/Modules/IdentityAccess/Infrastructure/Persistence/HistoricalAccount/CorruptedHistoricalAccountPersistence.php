<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use RuntimeException;
use Throwable;

final class CorruptedHistoricalAccountPersistence extends RuntimeException
{
    public static function detected(?Throwable $previous = null): self
    {
        return new self('The persisted historical Account is corrupted.', 0, $previous);
    }
}
