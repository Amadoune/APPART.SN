<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

use Appart\Modules\Media\Application\IngestionPersistence\AbstractIngestionState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaProcessingState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaQuotaState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaUploadState;
use JsonException;
use UnexpectedValueException;

final readonly class MediaIngestionStateMapper
{
    /** @param array<string, mixed> $row */
    public function upload(array $row): MediaUploadState
    {
        return $this->map($row, MediaUploadState::class);
    }

    /** @param array<string, mixed> $row */
    public function asset(array $row): MediaAssetState
    {
        return $this->map($row, MediaAssetState::class);
    }

    /** @param array<string, mixed> $row */
    public function processing(array $row): MediaProcessingState
    {
        return $this->map($row, MediaProcessingState::class);
    }

    /** @param array<string, mixed> $row */
    public function quota(array $row): MediaQuotaState
    {
        return $this->map($row, MediaQuotaState::class);
    }

    /**
     * @template T of AbstractIngestionState
     *
     * @param  array<string, mixed>  $row
     * @param  class-string<T>  $class
     * @return T
     */
    private function map(array $row, string $class): AbstractIngestionState
    {
        try {
            $payload = json_decode((string) $row['payload'], true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new UnexpectedValueException('Invalid Media Ingestion payload.', 0, $error);
        }
        if (! is_array($payload)) {
            throw new UnexpectedValueException('Invalid Media Ingestion payload.');
        }
        $state = new $class(
            (string) $row['aggregate_id'],
            (string) $row['state'],
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
            $payload,
        );
        if ($state->version < 1 || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1) {
            throw new UnexpectedValueException('Invalid Media Ingestion state.');
        }

        return $state;
    }
}
