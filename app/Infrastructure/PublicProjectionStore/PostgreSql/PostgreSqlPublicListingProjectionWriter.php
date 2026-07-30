<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicListingProjectionState;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use App\Application\PublicProjectionStore\PublicProjectionWatermarkRelation;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use InvalidArgumentException;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicListingProjectionWriter implements PublicListingProjectionWriter
{
    public function __construct(private PDO $connection, private PostgreSqlPublicListingProjectionMapper $mapper, private PostgreSqlPublicListingProjectionReader $reader) {}

    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        $this->requireState($record, PublicListingProjectionState::Current);

        return $this->transactional(fn (): PublicProjectionWriteResult => $this->generationState($record) === PublicProjectionGenerationState::Active ? $this->apply($record) : PublicProjectionWriteResult::GenerationMismatch);
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        $this->requireState($replacement, PublicListingProjectionState::Current);

        return $this->transactional(function () use ($previousCanonicalPath, $replacement): PublicProjectionWriteResult {
            if ($this->generationState($replacement) !== PublicProjectionGenerationState::Active) {
                return PublicProjectionWriteResult::GenerationMismatch;
            }
            if ($replacement->watermark->readiness()->value !== 'ready') {
                return PublicProjectionWriteResult::IncompleteWatermark;
            }
            $previous = $this->reader->snapshot($replacement->generationId, $previousCanonicalPath, true);
            $target = $this->reader->snapshot($replacement->generationId, $replacement->canonicalPath, true);
            if ($previous?->state === PublicListingProjectionState::Historical && $target?->state === PublicListingProjectionState::Current && $target->listingId === $replacement->listingId && $target->equivalentPayloadTo($replacement)) {
                return PublicProjectionWriteResult::AlreadyApplied;
            }
            if ($previous === null || $previous->state !== PublicListingProjectionState::Current || $previous->listingId !== $replacement->listingId) {
                return PublicProjectionWriteResult::HistoricalReservationConflict;
            }
            $relation = $replacement->watermark->compareTo($previous->watermark);
            if ($relation === PublicProjectionWatermarkRelation::Older) {
                return PublicProjectionWriteResult::RejectedObsolete;
            }
            if (in_array($relation, [PublicProjectionWatermarkRelation::Equal, PublicProjectionWatermarkRelation::Incomparable], true)) {
                return PublicProjectionWriteResult::DivergentWatermark;
            }
            $availability = $this->availability($replacement, $target);
            if ($availability !== null && $replacement->canonicalPath !== $previousCanonicalPath) {
                return $availability;
            }
            $this->save(PublicListingProjectionRecord::historical($replacement->listingId, $previousCanonicalPath, $replacement->watermark, $replacement->generationId));
            $this->save($replacement);

            return PublicProjectionWriteResult::Applied;
        });
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        $this->requireState($tombstone, PublicListingProjectionState::Tombstone);

        return $this->transactional(fn (): PublicProjectionWriteResult => $this->generationState($tombstone) === PublicProjectionGenerationState::Active ? $this->apply($tombstone) : PublicProjectionWriteResult::GenerationMismatch);
    }

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        $this->requireState($record, PublicListingProjectionState::Current);

        return $this->transactional(fn (): PublicProjectionWriteResult => $this->generationState($record) === PublicProjectionGenerationState::Candidate ? $this->apply($record) : PublicProjectionWriteResult::GenerationMismatch);
    }

    private function apply(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        if ($record->watermark->readiness()->value !== 'ready') {
            return PublicProjectionWriteResult::IncompleteWatermark;
        }
        $existing = $this->reader->forListing($record->generationId, $record->listingId, true);
        if ($existing !== null) {
            $relation = $record->watermark->compareTo($existing->watermark);
            if ($relation === PublicProjectionWatermarkRelation::Equal) {
                return $record->equivalentPayloadTo($existing) ? PublicProjectionWriteResult::AlreadyApplied : PublicProjectionWriteResult::DivergentWatermark;
            }
            if ($relation === PublicProjectionWatermarkRelation::Older) {
                return PublicProjectionWriteResult::RejectedObsolete;
            }
            if ($relation === PublicProjectionWatermarkRelation::Incomparable) {
                return PublicProjectionWriteResult::DivergentWatermark;
            }
            if ($existing->canonicalPath !== $record->canonicalPath) {
                return PublicProjectionWriteResult::CanonicalReplacementRequired;
            }
        }
        $target = $this->reader->snapshot($record->generationId, $record->canonicalPath, true);
        $availability = $this->availability($record, $target);
        if ($availability !== null) {
            return $availability;
        }
        $this->save($record);

        return PublicProjectionWriteResult::Applied;
    }

    private function availability(PublicListingProjectionRecord $candidate, ?PublicListingProjectionRecord $existing): ?PublicProjectionWriteResult
    {
        if ($existing === null || $existing->listingId === $candidate->listingId) {
            return null;
        }

        return $existing->state === PublicListingProjectionState::Current ? PublicProjectionWriteResult::CanonicalCollision : PublicProjectionWriteResult::HistoricalReservationConflict;
    }

    private function save(PublicListingProjectionRecord $record): void
    {
        $parameters = $this->mapper->parameters($record);
        $statement = $this->connection->prepare('INSERT INTO public_projection.listing_projections (generation_id,canonical_path,listing_id,state,read_model,listing_version,property_version,media_version,search_version,content_seo_version,public_geography_version,public_media_version,payload_checksum) VALUES (:generation,:canonical,:listing,:state,CASE WHEN :read_model = \'\' THEN NULL ELSE decode(:read_model,\'base64\') END,:lv,:pv,:mv,:sv,:cv,:gv,:uv,:checksum) ON CONFLICT (generation_id,canonical_path) DO UPDATE SET listing_id=EXCLUDED.listing_id,state=EXCLUDED.state,read_model=EXCLUDED.read_model,listing_version=EXCLUDED.listing_version,property_version=EXCLUDED.property_version,media_version=EXCLUDED.media_version,search_version=EXCLUDED.search_version,content_seo_version=EXCLUDED.content_seo_version,public_geography_version=EXCLUDED.public_geography_version,public_media_version=EXCLUDED.public_media_version,payload_checksum=EXCLUDED.payload_checksum,updated_at=clock_timestamp()');
        $parameters['read_model'] ??= '';
        $statement->execute($parameters);
    }

    private function generationState(PublicListingProjectionRecord $record): ?PublicProjectionGenerationState
    {
        $statement = $this->connection->prepare('SELECT state FROM public_projection.generations WHERE generation_id=:generation FOR UPDATE');
        $statement->execute(['generation' => $record->generationId->value]);
        $state = $statement->fetchColumn();

        return is_string($state) ? PublicProjectionGenerationState::from($state) : null;
    }

    private function requireState(PublicListingProjectionRecord $record, PublicListingProjectionState $state): void
    {
        if ($record->state !== $state) {
            throw new InvalidArgumentException('Public projection write intent does not match record state.');
        }
    }

    private function transactional(callable $operation): PublicProjectionWriteResult
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
}
