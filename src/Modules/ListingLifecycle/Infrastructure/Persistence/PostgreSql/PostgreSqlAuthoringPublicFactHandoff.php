<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicTransactionKind;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublishedPublicFacts;
use DateTimeImmutable;
use PDO;
use Throwable;

final readonly class PostgreSqlAuthoringPublicFactHandoff implements AuthoringPublicFactHandoffV1
{
    private const string SAVEPOINT = 'authoring_public_fact_handoff';

    public function __construct(private PDO $connection) {}

    public function prepare(AuthoringPublicFactSnapshot $snapshot): PublicFactHandoffResult
    {
        return $this->transactional(function () use ($snapshot): PublicFactHandoffResult {
            $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(:listing, 0))')->execute(['listing' => $snapshot->listingId]);
            $current = $this->row($snapshot->listingId, true);
            if ($current !== null) {
                if ((string) $current['source_intent_id'] === $snapshot->sourceIntentId) {
                    return hash_equals((string) $current['candidate_checksum'], $snapshot->checksum()) ? PublicFactHandoffResult::AlreadyApplied : PublicFactHandoffResult::DivergentIntent;
                }

                return PublicFactHandoffResult::VersionConflict;
            }
            $statement = $this->connection->prepare('INSERT INTO listing_lifecycle.authoring_public_fact_handoffs (listing_id,authoring_version,transaction_kind,source_intent_id,source_checksum,observed_at,candidate_checksum,status) VALUES (:listing,:version,:transaction,:intent,:source_checksum,:observed_at,:candidate_checksum,\'candidate\')');
            $statement->execute(['listing' => $snapshot->listingId, 'version' => $snapshot->authoringVersion, 'transaction' => $snapshot->transactionKind->value, 'intent' => $snapshot->sourceIntentId, 'source_checksum' => $snapshot->sourceChecksum, 'observed_at' => $snapshot->observedAt->format('Y-m-d H:i:s.uP'), 'candidate_checksum' => $snapshot->checksum()]);

            return PublicFactHandoffResult::Applied;
        });
    }

    public function candidate(string $listingId): ?AuthoringPublicFactSnapshot
    {
        $row = $this->row($listingId);
        if ($row === null || (string) $row['status'] !== 'candidate') {
            return null;
        }

        return $this->snapshot($row);
    }

    public function seal(string $listingId, string $publishedRevisionId, DateTimeImmutable $publishedAt): PublicFactHandoffResult
    {
        return $this->transactional(function () use ($listingId, $publishedRevisionId, $publishedAt): PublicFactHandoffResult {
            $row = $this->row($listingId, true);
            if ($row === null) {
                return PublicFactHandoffResult::MissingCandidate;
            }
            if ((string) $row['status'] === 'published') {
                return (string) $row['published_revision_id'] === $publishedRevisionId ? PublicFactHandoffResult::AlreadyApplied : PublicFactHandoffResult::VersionConflict;
            }
            $statement = $this->connection->prepare("UPDATE listing_lifecycle.authoring_public_fact_handoffs SET status='published',published_revision_id=:revision,published_at=:published_at WHERE listing_id=:listing AND status='candidate'");
            $statement->execute(['revision' => $publishedRevisionId, 'published_at' => $publishedAt->format('Y-m-d H:i:s.uP'), 'listing' => $listingId]);

            return $statement->rowCount() === 1 ? PublicFactHandoffResult::Applied : PublicFactHandoffResult::VersionConflict;
        });
    }

    public function published(string $listingId): ?PublishedPublicFacts
    {
        $row = $this->row($listingId);
        if ($row === null || (string) $row['status'] !== 'published' || $row['published_revision_id'] === null || $row['published_at'] === null) {
            return null;
        }

        return new PublishedPublicFacts($listingId, PublicTransactionKind::from((string) $row['transaction_kind']), (string) $row['published_revision_id'], new DateTimeImmutable((string) $row['published_at']));
    }

    /** @return array<string, mixed>|null */
    private function row(string $listingId, bool $lock = false): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM listing_lifecycle.authoring_public_fact_handoffs WHERE listing_id=:listing'.($lock ? ' FOR UPDATE' : ''));
        $statement->execute(['listing' => $listingId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /** @param array<string, mixed> $row */
    private function snapshot(array $row): AuthoringPublicFactSnapshot
    {
        return new AuthoringPublicFactSnapshot((string) $row['listing_id'], (int) $row['authoring_version'], PublicTransactionKind::from((string) $row['transaction_kind']), (string) $row['source_intent_id'], (string) $row['source_checksum'], new DateTimeImmutable((string) $row['observed_at']));
    }

    private function transactional(callable $operation): PublicFactHandoffResult
    {
        $owner = ! $this->connection->inTransaction();
        $owner ? $this->connection->beginTransaction() : $this->connection->exec('SAVEPOINT '.self::SAVEPOINT);
        try {
            $result = $operation();
            $owner ? $this->connection->commit() : $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);

            return $result;
        } catch (Throwable) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec('ROLLBACK TO SAVEPOINT '.self::SAVEPOINT);
                $this->connection->exec('RELEASE SAVEPOINT '.self::SAVEPOINT);
            }

            return PublicFactHandoffResult::DependencyUnavailable;
        }
    }
}
