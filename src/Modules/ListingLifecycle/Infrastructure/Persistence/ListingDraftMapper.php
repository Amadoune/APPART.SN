<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use UnexpectedValueException;

final readonly class ListingDraftMapper
{
    /** @param array<string, mixed> $row */
    public function toState(array $row): ListingDraftState
    {
        $state = new ListingDraftState(
            (string) $row['listing_id'],
            (string) $row['property_id'],
            (string) $row['title'],
            (string) $row['description'],
            (string) $row['transaction_kind'],
            $row['price_minor'] === null ? null : (int) $row['price_minor'],
            $row['currency'] === null ? null : (string) $row['currency'],
            $row['charges_minor'] === null ? null : (int) $row['charges_minor'],
            $row['availability_date'] === null ? null : (string) $row['availability_date'],
            (string) $row['contact_preference'],
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
        );
        if ($state->version < 1 || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1) {
            throw new UnexpectedValueException('Invalid Listing Draft persistence state.');
        }

        return $state;
    }
}
