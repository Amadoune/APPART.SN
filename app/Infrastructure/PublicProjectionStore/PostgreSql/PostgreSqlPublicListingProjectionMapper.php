<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicListingProjectionState;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\ReadModels\PublicListingReadModel;
use DateTimeImmutable;
use RuntimeException;

final readonly class PostgreSqlPublicListingProjectionMapper
{
    /** @return array<string, int|string|null> */
    public function parameters(PublicListingProjectionRecord $record): array
    {
        $payload = $record->readModel === null ? null : base64_encode(serialize($record->readModel));

        return [
            'generation' => $record->generationId->value,
            'canonical' => $record->canonicalPath,
            'listing' => $record->listingId,
            'state' => $record->state->value,
            'read_model' => $payload,
            'lv' => $record->watermark->listingVersion,
            'pv' => $record->watermark->propertyVersion,
            'mv' => $record->watermark->mediaVersion,
            'sv' => $record->watermark->searchVersion,
            'cv' => $record->watermark->contentSeoVersion,
            'gv' => $record->watermark->publicGeographyVersion,
            'uv' => $record->watermark->publicMediaVersion,
            'checksum' => hash('sha256', serialize($record)),
        ];
    }

    /** @param array<string, mixed> $row */
    public function toRecord(array $row): PublicListingProjectionRecord
    {
        $watermark = new PublicProjectionWatermark((int) $row['listing_version'], (int) $row['property_version'], (int) $row['media_version'], (int) $row['search_version'], (int) $row['content_seo_version'], $row['public_geography_version'] === null ? null : (int) $row['public_geography_version'], $row['public_media_version'] === null ? null : (int) $row['public_media_version']);
        $generation = PublicProjectionGenerationId::fromString((string) $row['generation_id']);
        $state = PublicListingProjectionState::from((string) $row['state']);
        if ($state === PublicListingProjectionState::Current) {
            $encoded = is_resource($row['read_model']) ? stream_get_contents($row['read_model']) : $row['read_model'];
            $model = is_string($encoded) ? unserialize($encoded, ['allowed_classes' => [PublicListingReadModel::class, DateTimeImmutable::class]]) : false;
            if (! $model instanceof PublicListingReadModel) {
                throw new RuntimeException('Corrupt public listing read model payload.');
            }

            $record = PublicListingProjectionRecord::current((string) $row['listing_id'], (string) $row['canonical_path'], $model, $watermark, $generation);
        } else {
            $record = $state === PublicListingProjectionState::Historical
                ? PublicListingProjectionRecord::historical((string) $row['listing_id'], (string) $row['canonical_path'], $watermark, $generation)
                : PublicListingProjectionRecord::tombstone((string) $row['listing_id'], (string) $row['canonical_path'], $watermark, $generation);
        }

        if (! hash_equals((string) $row['payload_checksum'], hash('sha256', serialize($record)))) {
            throw new RuntimeException('Corrupt public listing projection checksum.');
        }

        return $record;
    }
}
