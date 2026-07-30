<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use UnexpectedValueException;

final readonly class ListingOwnershipMapper
{
    /** @param array<string, mixed> $row
     * @param  array<string, list<string>>  $delegations
     */
    public function toState(array $row, array $delegations): ListingOwnershipState
    {
        $state = new ListingOwnershipState(
            (string) $row['listing_id'],
            (string) $row['property_id'],
            (string) $row['owner_account_id'],
            $delegations,
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
        );
        if ($state->version < 1 || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1) {
            throw new UnexpectedValueException('Invalid Listing Ownership persistence state.');
        }

        return $state;
    }
}
