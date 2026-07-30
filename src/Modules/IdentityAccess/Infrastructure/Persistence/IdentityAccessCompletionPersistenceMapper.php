<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence;

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use InvalidArgumentException;

final readonly class IdentityAccessCompletionPersistenceMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $columns
     */
    public function snapshot(
        array $row,
        string $identityColumn,
        array $columns,
        string $intentColumn = 'last_intent_id',
        string $checksumColumn = 'last_intent_checksum',
        string $versionColumn = 'version',
    ): OwnerPersistenceState {
        $identity = $row[$identityColumn] ?? null;
        $version = $row[$versionColumn] ?? null;
        $intent = $row[$intentColumn] ?? null;
        $checksum = $row[$checksumColumn] ?? null;
        if (! is_string($identity) || ! is_numeric($version) || ! is_string($intent) || ! is_string($checksum)) {
            throw new InvalidArgumentException('Corrupted Identity & Access completion persistence row.');
        }

        $values = [];
        foreach ($columns as $column) {
            $value = $row[$column] ?? null;
            if (! is_bool($value) && ! is_int($value) && ! is_string($value) && $value !== null) {
                throw new InvalidArgumentException('Unsupported persistence value.');
            }
            $values[$column] = $value;
        }

        return new OwnerPersistenceState($identity, (int) $version, $intent, $checksum, $values);
    }
}
