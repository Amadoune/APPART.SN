<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence;

use InvalidArgumentException;

/**
 * Persistence port state. Values are already protected and normalized by
 * their owning authority; the contract contains no infrastructure concern.
 */
final readonly class OwnerPersistenceState
{
    /** @param array<string, bool|int|string|null> $values */
    public function __construct(
        public string $identity,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public array $values,
    ) {
        if ($identity === '' || $version < 1) {
            throw new InvalidArgumentException('A persisted identity and a positive version are required.');
        }
        if (preg_match('/^[0-9a-f]{64}$/', $intentChecksum) !== 1) {
            throw new InvalidArgumentException('The deterministic intent checksum must be lowercase SHA-256.');
        }
    }
}
