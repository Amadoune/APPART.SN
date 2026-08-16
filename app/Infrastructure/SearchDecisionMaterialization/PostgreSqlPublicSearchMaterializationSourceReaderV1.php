<?php

namespace App\Infrastructure\SearchDecisionMaterialization;

use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationSourceResult;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationSources;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceKind;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;
use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlPublicSearchMaterializationSourceReaderV1 implements PublicSearchMaterializationSourceReaderV1
{
    public function __construct(private PDO $connection) {}

    public function read(ListingId $listingId): PublicSearchMaterializationSourceResult
    {
        try {
            $listing = $this->one('SELECT property_id::text,status FROM listing_lifecycle.listings WHERE id=CAST(:id AS uuid)', ['id' => $listingId->value]);
            if ($listing === null) {
                return PublicSearchMaterializationSourceResult::missing();
            }
            $revision = $this->one('SELECT sequence,revision_id::text,occurred_at::text,status FROM listing_lifecycle.listing_revisions WHERE listing_id=CAST(:id AS uuid) ORDER BY sequence DESC LIMIT 1', ['id' => $listingId->value]);
            $property = $this->one('SELECT id::text,status FROM real_estate_catalog.properties WHERE id=CAST(:id AS uuid)', ['id' => $listing['property_id']]);
            $promotion = $this->one("SELECT command_id::text,authoring_version,occurred_at::text,result FROM real_estate_catalog.property_promotion_commands WHERE property_id=CAST(:id AS uuid) AND result='applied'", ['id' => $listing['property_id']]);
            if ($revision === null || $property === null || $promotion === null) {
                return PublicSearchMaterializationSourceResult::missing();
            }
            if ($revision['status'] !== $listing['status']) {
                return PublicSearchMaterializationSourceResult::corrupted();
            }

            $collections = $this->all('SELECT id::text,version,last_changed_at::text FROM media.media_collections WHERE property_id=CAST(:id AS uuid)', ['id' => $listing['property_id']]);
            if (count($collections) === 0) {
                return PublicSearchMaterializationSourceResult::missing();
            }
            if (count($collections) !== 1 || (int) $collections[0]['version'] < 1) {
                return PublicSearchMaterializationSourceResult::corrupted();
            }
            $media = $collections[0];
            $primary = $this->all("SELECT media_id::text FROM media.media_items WHERE collection_id=CAST(:id AS uuid) AND status='active' AND is_primary=true", ['id' => $media['id']]);
            if (count($primary) > 1) {
                return PublicSearchMaterializationSourceResult::corrupted();
            }

            return PublicSearchMaterializationSourceResult::found(new PublicSearchMaterializationSources(
                $this->listingState((string) $listing['status']),
                match ((string) $property['status']) {
                    'active' => PropertySearchState::Available,
                    'archived' => PropertySearchState::Archived,
                    default => throw new \UnexpectedValueException('Unsupported Property state.'),
                },
                count($primary) === 1 ? MediaSearchState::Ready : MediaSearchState::MissingPrimary,
                SourceRevision::create(SourceKind::Listing, (int) $revision['sequence'], (string) $revision['revision_id'], new DateTimeImmutable((string) $revision['occurred_at'])),
                SourceRevision::create(SourceKind::Property, (int) $promotion['authoring_version'], (string) $promotion['command_id'], new DateTimeImmutable((string) $promotion['occurred_at'])),
                SourceRevision::create(SourceKind::Media, (int) $media['version'], (string) $media['id'], new DateTimeImmutable((string) $media['last_changed_at'])),
            ));
        } catch (PDOException) {
            return PublicSearchMaterializationSourceResult::dependencyUnavailable();
        } catch (Throwable) {
            return PublicSearchMaterializationSourceResult::corrupted();
        }
    }

    private function listingState(string $status): ListingSearchState
    {
        return match ($status) {
            'published' => ListingSearchState::Published,
            'expired', 'withdrawn', 'rejected', 'archived' => ListingSearchState::Terminal,
            'draft', 'submitted', 'under_review', 'changes_requested', 'suspended' => ListingSearchState::TemporarilyUnavailable,
            default => throw new \UnexpectedValueException('Unsupported Listing state.'),
        };
    }

    /** @param array<string, scalar> $parameters
     * @return array<string, mixed>|null
     */
    private function one(string $sql, array $parameters): ?array
    {
        $rows = $this->all($sql, $parameters);

        return $rows[0] ?? null;
    }

    /** @param array<string, scalar> $parameters
     * @return list<array<string, mixed>>
     */
    private function all(string $sql, array $parameters): array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
