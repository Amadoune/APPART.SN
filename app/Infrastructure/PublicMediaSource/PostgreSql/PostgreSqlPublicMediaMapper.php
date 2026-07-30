<?php

namespace App\Infrastructure\PublicMediaSource\PostgreSql;

use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaRevision\PublicMediaRevisionChecksum;
use App\Application\PublicMediaRevision\PublicMediaRevisionVersion;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicMediaSource\PublicMediaVariant;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlPublicMediaMapper
{
    /** @return array<string, int|string> */
    public function parameters(PublicMediaDecision $decision): array
    {
        $payload = $decision->canonicalPayload();

        return [
            'media_collection_id' => $decision->mediaCollectionId,
            'version' => $decision->revision->version->value,
            'causation' => $decision->revision->causationKey,
            'revision_checksum' => $decision->revision->checksum->value,
            'payload' => $payload,
            'payload_checksum' => hash('sha256', $payload),
        ];
    }

    /** @param array<string, mixed> $row */
    public function toDecision(array $row): PublicMediaDecision
    {
        try {
            $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload) || ! array_key_exists('cover', $payload) || ! array_key_exists('gallery', $payload) || ! is_array($payload['gallery'])) {
                throw new RuntimeException('Invalid public Media payload.');
            }

            $cover = $payload['cover'] === null ? null : $this->item($payload['cover']);
            $gallery = [];
            foreach ($payload['gallery'] as $item) {
                $gallery[] = $this->item($item);
            }

            $revision = new PublicMediaRevision(
                PublicMediaRevisionVersion::fromInt((int) $row['version']),
                PublicMediaRevisionChecksum::fromString((string) $row['revision_checksum']),
                (string) $row['causation_key'],
            );
            $decision = new PublicMediaDecision((string) $row['media_collection_id'], $revision, $cover, $gallery);
            $parameters = $this->parameters($decision);
            if (! hash_equals((string) $row['payload_checksum'], $parameters['payload_checksum'])) {
                throw new RuntimeException('Corrupt public Media checksum.');
            }

            return $decision;
        } catch (Throwable $error) {
            throw new RuntimeException('Corrupt public Media decision.', 0, $error);
        }
    }

    private function item(mixed $data): PublicMediaItem
    {
        if (! is_array($data) || ! is_array($data['variants'] ?? null)) {
            throw new RuntimeException('Invalid public Media item.');
        }

        $variants = [];
        foreach ($data['variants'] as $variant) {
            if (! is_array($variant)) {
                throw new RuntimeException('Invalid public Media variant.');
            }
            $variants[] = new PublicMediaVariant((string) ($variant['name'] ?? ''), (string) ($variant['url'] ?? ''));
        }

        return new PublicMediaItem((string) ($data['mediaId'] ?? ''), (string) ($data['url'] ?? ''), $variants);
    }
}
