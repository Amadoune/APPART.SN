<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
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
            isset($row['property_type']) ? (string) $row['property_type'] : null,
            isset($row['city']) ? (string) $row['city'] : null,
            isset($row['neighborhood']) ? (string) $row['neighborhood'] : null,
        );
        if ($state->version < 1
            || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1
            || ($state->propertyType !== null && PropertyType::tryFrom($state->propertyType) === null)
            || ($state->city !== null && trim($state->city) === '')
            || ($state->neighborhood !== null && trim($state->neighborhood) === '')) {
            throw new UnexpectedValueException('Invalid Property Authoring persistence state.');
        }

        return $state;
    }
}
