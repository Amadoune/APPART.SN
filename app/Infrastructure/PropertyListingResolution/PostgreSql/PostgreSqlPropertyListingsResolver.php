<?php

namespace App\Infrastructure\PropertyListingResolution\PostgreSql;

use App\Application\PropertyListingResolution\Contract\PropertyListingsResolver;
use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use App\Application\PropertyListingResolution\PropertyListingsPage;
use App\Application\PropertyListingResolution\PropertyListingsPageStatus;
use App\Infrastructure\PropertyListingResolution\PropertyListingsCheckpoint;
use PDO;
use Throwable;

final readonly class PostgreSqlPropertyListingsResolver implements PropertyListingsResolver
{
    public function __construct(private PDO $connection, private PropertyListingsCheckpoint $checkpoints) {}

    public function readPage(string $propertyId, ?string $checkpoint, int $limit): PropertyListingsPage
    {
        if (! self::isUuid($propertyId)) {
            return $this->failure(PropertyListingsPageStatus::InvalidIdentity, PropertyListingsDiagnostic::InvalidPropertyIdentity);
        }
        if ($limit < 1) {
            return $this->failure(PropertyListingsPageStatus::Corrupted, PropertyListingsDiagnostic::InvalidLimit);
        }

        $after = null;
        if ($checkpoint !== null) {
            $decoded = $this->checkpoints->decode($propertyId, $checkpoint);
            if ($decoded['diagnostic'] !== PropertyListingsDiagnostic::None) {
                return $this->failure(PropertyListingsPageStatus::Corrupted, $decoded['diagnostic']);
            }
            $after = $decoded['after'] ?? null;
        }

        try {
            $sql = 'SELECT id::text FROM listing_lifecycle.listings WHERE property_id = CAST(:property_id AS uuid)'
                .($after === null ? '' : ' AND id > CAST(:after_id AS uuid)')
                .' ORDER BY id LIMIT '.($limit + 1);
            $statement = $this->connection->prepare($sql);
            $parameters = ['property_id' => $propertyId];
            if ($after !== null) {
                $parameters['after_id'] = $after;
            }
            $statement->execute($parameters);
            $ids = array_values(array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN)));
        } catch (Throwable) {
            return $this->failure(PropertyListingsPageStatus::Corrupted, PropertyListingsDiagnostic::PersistedIdentityNotMappable);
        }

        foreach ($ids as $id) {
            if (! self::isUuid($id)) {
                return $this->failure(PropertyListingsPageStatus::Corrupted, PropertyListingsDiagnostic::PersistedIdentityNotMappable);
            }
        }
        $hasMore = count($ids) > $limit;
        if ($hasMore) {
            array_pop($ids);
        }
        if ($ids === []) {
            return new PropertyListingsPage(PropertyListingsPageStatus::Empty, [], null, true);
        }
        if (! $hasMore) {
            return new PropertyListingsPage(PropertyListingsPageStatus::Completed, $ids, null, true);
        }

        return new PropertyListingsPage(
            PropertyListingsPageStatus::Found,
            $ids,
            $this->checkpoints->encode($propertyId, $ids[array_key_last($ids)]),
            false,
        );
    }

    private function failure(PropertyListingsPageStatus $status, PropertyListingsDiagnostic $diagnostic): PropertyListingsPage
    {
        return new PropertyListingsPage($status, [], null, true, $diagnostic);
    }

    private static function isUuid(string $identity): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $identity) === 1;
    }
}
