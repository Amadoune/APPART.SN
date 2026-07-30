<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use UnexpectedValueException;

final readonly class PropertyAuthoringMapper
{
    /** @param array<string, mixed> $row */
    public function toState(array $row): PropertyAuthoringState
    {
        $state = new PropertyAuthoringState(
            (string) $row['property_id'],
            (string) $row['owner_account_id'],
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
        );
        if ($state->version < 1 || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1) {
            throw new UnexpectedValueException('Invalid Property Authoring persistence state.');
        }

        return $state;
    }
}
