<?php

namespace App\Infrastructure\ContentSeoSnapshotMaterialization;

use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationSourceResult;
use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationSources;
use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoSnapshotIdentityV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoMaterializationSourceReaderV1;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\PropertySeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SearchSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadStatus;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId as SearchListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlContentSeoMaterializationSourceReaderV1 implements ContentSeoMaterializationSourceReaderV1
{
    public function __construct(private PDO $connection, private SearchDecisionReader $search, private ContentSeoSnapshotIdentityV1 $identities) {}

    public function read(ListingId $listingId): ContentSeoMaterializationSourceResult
    {
        try {
            $row = $this->one('SELECT l.property_id::text,l.status,l.expiration_date::text,r.sequence,r.revision_id::text,r.status AS revision_status,r.occurred_at::text,d.version AS draft_version,d.title,d.description,d.last_intent_id::text,d.last_intent_checksum,h.authoring_version,h.source_intent_id::text,h.source_checksum,h.status AS handoff_status,h.published_revision_id::text,h.published_at::text,p.status AS property_status,p.type AS property_type,pc.command_id::text AS promotion_id,pc.authoring_version AS property_version,pc.occurred_at::text AS property_at,g.id::text AS place_id,g.official_name AS city,g.aggregate_version,g.enabled,g.type AS place_type FROM listing_lifecycle.listings l JOIN LATERAL (SELECT * FROM listing_lifecycle.listing_revisions WHERE listing_id=l.id ORDER BY sequence DESC LIMIT 1) r ON true JOIN listing_authoring.drafts d ON d.listing_id=l.id JOIN listing_lifecycle.authoring_public_fact_handoffs h ON h.listing_id=l.id JOIN real_estate_catalog.properties p ON p.id=l.property_id JOIN LATERAL (SELECT * FROM real_estate_catalog.property_promotion_commands WHERE property_id=p.id AND result=\'applied\' ORDER BY authoring_version DESC LIMIT 1) pc ON true JOIN real_estate_catalog.property_addresses a ON a.property_id=p.id JOIN geography.places g ON g.id=CAST(a.geographic_place_id AS uuid) WHERE l.id=CAST(:id AS uuid)', ['id' => $listingId->value]);
            if ($row === null) {
                return ContentSeoMaterializationSourceResult::missing();
            }
            if ($row['status'] !== 'published' || $row['revision_status'] !== 'published' || $row['handoff_status'] !== 'published') {
                return ContentSeoMaterializationSourceResult::notReady();
            }
            if ($row['published_revision_id'] !== $row['revision_id'] || (int) $row['draft_version'] !== (int) $row['authoring_version'] || $row['place_type'] !== 'city' || ! $this->boolean($row['enabled'])) {
                return ContentSeoMaterializationSourceResult::corrupted();
            }
            $search = $this->search->readByListing(SearchListingId::fromString($listingId->value));
            if ($search->status === SearchDecisionReadStatus::Missing) {
                return ContentSeoMaterializationSourceResult::missing();
            }
            if ($search->status !== SearchDecisionReadStatus::Found || $search->decision === null) {
                return ContentSeoMaterializationSourceResult::corrupted();
            }
            $decision = $search->decision;
            $searchFacts = [];
            foreach ($decision->projection->revisions->all() as $revision) {
                $searchFacts[] = $revision->source->value;
                $searchFacts[] = $revision->version;
                $searchFacts[] = $revision->factId;
                $searchFacts[] = $revision->effectiveAt->format('Y-m-d\TH:i:s.uP');
            }
            $coherence = $this->identities->coherenceId([
                $listingId->value, (int) $row['sequence'], (string) $row['revision_id'], (int) $row['draft_version'], (string) $row['last_intent_id'], (string) $row['last_intent_checksum'],
                (string) $row['source_intent_id'], (string) $row['source_checksum'], (string) $row['promotion_id'], (int) $row['property_version'], (string) $row['property_type'],
                (string) $row['place_id'], (int) $row['aggregate_version'], (string) $row['city'], $decision->decisionId->value, $decision->version, ...$searchFacts,
            ]);
            $publishedAt = new DateTimeImmutable((string) $row['published_at']);
            $searchAt = $publishedAt;
            foreach ($decision->projection->revisions->all() as $revision) {
                if ($revision->effectiveAt > $searchAt) {
                    $searchAt = $revision->effectiveAt;
                }
            }

            return ContentSeoMaterializationSourceResult::found(new ContentSeoMaterializationSources(
                new ListingSeoSource($listingId, ListingSeoState::Published, (string) $row['title'], (string) $row['description'], '', SeoSourceRevision::create(SeoSourceKind::Listing, (int) $row['sequence'], (string) $row['revision_id'], $coherence, new DateTimeImmutable((string) $row['occurred_at'])), $publishedAt, new DateTimeImmutable((string) $row['expiration_date'])),
                new SearchSeoSource($listingId, $decision->projection->state === ProjectionState::Visible ? SearchSeoState::Public : SearchSeoState::Hidden, SeoSourceRevision::create(SeoSourceKind::Search, $decision->version, $decision->decisionId->value, $coherence, $searchAt)),
                new PropertySeoSource($listingId, $row['property_status'] === 'active' ? PropertySeoState::Available : PropertySeoState::Archived, (string) $row['property_type'], (string) $row['city'], SeoSourceRevision::create(SeoSourceKind::Property, (int) $row['property_version'], (string) $row['promotion_id'], $coherence, new DateTimeImmutable((string) $row['property_at']))),
                $publishedAt,
            ));
        } catch (PDOException) {
            return ContentSeoMaterializationSourceResult::dependencyUnavailable();
        } catch (Throwable) {
            return ContentSeoMaterializationSourceResult::corrupted();
        }
    }

    /** @param array<string, scalar> $parameters
     * @return array<string, mixed>|null
     */
    private function one(string $sql, array $parameters): ?array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function boolean(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 't';
    }
}
