<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use DateTimeImmutable;
use LogicException;

final readonly class CredentialPersistenceSnapshotV1
{
    public function __construct(
        public SensitivePersistenceValueV1 $encodedPasswordHash,
        public DateTimeImmutable $changedAt,
    ) {}

    public function __serialize(): array
    {
        throw new LogicException('Credential persistence snapshots cannot be serialized.');
    }

    public function __debugInfo(): array
    {
        return ['encodedPasswordHash' => '[REDACTED]', 'changedAt' => $this->changedAt];
    }
}
