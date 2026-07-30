<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Application\Creation\ListingCreationIntent;
use PDO;
use RuntimeException;

final readonly class PostgreSqlListingCreationIntentStore implements ListingCreationIntentStore
{
    public function __construct(private PDO $connection) {}

    public function reserve(ListingCreationIntent $intent): bool
    {
        $statement = $this->connection->prepare("INSERT INTO listing_lifecycle.listing_creation_intents (operation, intent_id, checksum, listing_id, property_id, result, aggregate_version, created_at) VALUES ('CreateListingDraftV1', :intent_id, :checksum, :listing_id, :property_id, 'pending', NULL, CURRENT_TIMESTAMP) ON CONFLICT (operation, intent_id) DO NOTHING");
        $statement->execute([
            'intent_id' => $intent->intentId,
            'checksum' => $intent->checksum,
            'listing_id' => $intent->listingId,
            'property_id' => $intent->propertyId,
        ]);

        return $statement->rowCount() === 1;
    }

    public function find(string $intentId): ?ListingCreationIntent
    {
        $statement = $this->connection->prepare("SELECT checksum, listing_id, property_id, result, aggregate_version FROM listing_lifecycle.listing_creation_intents WHERE operation = 'CreateListingDraftV1' AND intent_id = :intent_id");
        $statement->execute(['intent_id' => $intentId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        if ($row['result'] !== 'applied') {
            throw new RuntimeException('Listing creation intent is incomplete.');
        }

        return new ListingCreationIntent(
            $intentId,
            (string) $row['checksum'],
            (string) $row['listing_id'],
            (string) $row['property_id'],
            (int) $row['aggregate_version'],
        );
    }

    public function markApplied(string $intentId, int $aggregateVersion): void
    {
        $statement = $this->connection->prepare("UPDATE listing_lifecycle.listing_creation_intents SET result = 'applied', aggregate_version = :aggregate_version WHERE operation = 'CreateListingDraftV1' AND intent_id = :intent_id AND result = 'pending'");
        $statement->execute(['intent_id' => $intentId, 'aggregate_version' => $aggregateVersion]);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Listing creation intent cannot be completed.');
        }
    }
}
