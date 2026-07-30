<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilityDecisionMaterializer;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterializationResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadEligibilitySourceDataMapper;
use PDO;
use Throwable;

final readonly class PostgreSqlLeadEligibilityDecisionStore implements LeadEligibilityDecisionMaterializer, LeadEligibilitySourceDataReader
{
    public function __construct(private PDO $connection, private LeadEligibilitySourceDataMapper $mapper) {}

    public function materialize(LeadEligibilityMaterialization $materialization): LeadEligibilityMaterializationResult
    {
        if (! $materialization->listingRevision->equals($materialization->advertiserRevision)
            || $materialization->listingRevision->effectiveAt->value->getOffset() !== 0
            || $materialization->advertiserRevision->effectiveAt->value->getOffset() !== 0) {
            return LeadEligibilityMaterializationResult::IncoherentRevision;
        }

        return $this->withinTransaction(function () use ($materialization): LeadEligibilityMaterializationResult {
            $this->lock($materialization->listingId);
            $current = $this->currentRow($materialization->listingId, true);
            $row = $this->mapper->toRow($materialization);

            if ($current === false) {
                if ($materialization->listingRevision->version !== 1) {
                    return LeadEligibilityMaterializationResult::ContinuityConflict;
                }
                $this->insert($row);

                return LeadEligibilityMaterializationResult::Created;
            }

            $currentVersion = (int) $current['version'];
            $version = $materialization->listingRevision->version;
            if ($version < $currentVersion) {
                return LeadEligibilityMaterializationResult::StaleVersion;
            }
            if ($version === $currentVersion) {
                if (hash_equals((string) $current['materialization_checksum'], $row['checksum'])) {
                    return LeadEligibilityMaterializationResult::AlreadyMaterialized;
                }
                if (($current['normative_advertiser_id'] ?? null) !== $row['normative_advertiser_id']) {
                    return LeadEligibilityMaterializationResult::RelationDivergence;
                }

                return LeadEligibilityMaterializationResult::DivergentVersion;
            }
            if ($version !== $currentVersion + 1
                || new \DateTimeImmutable($row['effective_at']) <= new \DateTimeImmutable((string) $current['effective_at'])) {
                return LeadEligibilityMaterializationResult::ContinuityConflict;
            }

            $this->insert($row);

            return LeadEligibilityMaterializationResult::Created;
        });
    }

    public function current(ListingId $listingId): LeadEligibilitySourceReadResult
    {
        $row = $this->currentRow($listingId, false);
        if ($row === false) {
            return LeadEligibilitySourceReadResult::sourceAbsent($listingId);
        }
        try {
            return LeadEligibilitySourceReadResult::found($this->mapper->toRecord($row));
        } catch (Throwable) {
            return LeadEligibilitySourceReadResult::corrupted($listingId);
        }
    }

    public function history(ListingId $listingId): array
    {
        $statement = $this->connection->prepare($this->selectSql().' WHERE listing_id=:listing_id ORDER BY version ASC');
        $statement->execute(['listing_id' => $listingId->value]);

        return array_map(
            fn (array $row): LeadEligibilitySourceRecord => $this->mapper->toRecord($row),
            $statement->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    /** @param callable(): LeadEligibilityMaterializationResult $operation */
    private function withinTransaction(callable $operation): LeadEligibilityMaterializationResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function lock(ListingId $listingId): void
    {
        $statement = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:listing_id,0))');
        $statement->execute(['listing_id' => $listingId->value]);
    }

    /** @return array<string, mixed>|false */
    private function currentRow(ListingId $listingId, bool $forUpdate): array|false
    {
        $sql = $this->selectSql().' WHERE listing_id=:listing_id ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['listing_id' => $listingId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT listing_id::text,version,normative_advertiser_id::text,listing_decision,evaluated_advertiser_id::text,advertiser_decision,coherence_id::text,effective_at,materialization_checksum FROM contacts_leads.lead_eligibility_decisions';
    }

    /** @param array<string, mixed> $row */
    private function insert(array $row): void
    {
        $statement = $this->connection->prepare('INSERT INTO contacts_leads.lead_eligibility_decisions(listing_id,version,normative_advertiser_id,listing_decision,evaluated_advertiser_id,advertiser_decision,coherence_id,effective_at,materialization_checksum) VALUES(CAST(:listing_id AS uuid),:version,CAST(:normative_advertiser_id AS uuid),:listing_decision,CAST(:evaluated_advertiser_id AS uuid),:advertiser_decision,CAST(:coherence_id AS uuid),CAST(:effective_at AS timestamptz),:checksum)');
        $statement->execute($row);
    }
}
