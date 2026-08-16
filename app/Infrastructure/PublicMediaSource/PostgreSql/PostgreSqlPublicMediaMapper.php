<?php

namespace App\Infrastructure\PublicMediaSource\PostgreSql;

use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaRevision\PublicMediaRevisionChecksum;
use App\Application\PublicMediaRevision\PublicMediaRevisionVersion;
use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaItem;
use App\Application\PublicMediaSource\PublicMediaItemV2;
use App\Application\PublicMediaSource\PublicMediaSourceRevisionV2;
use App\Application\PublicMediaSource\PublicMediaVariant;
use RuntimeException;
use Throwable;

final readonly class PostgreSqlPublicMediaMapper
{
    public function __construct(private ?string $publicOrigin = null) {}

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
            if (is_array($payload) && ($payload['schemaVersion'] ?? null) === 2) {
                return $this->v2($row, $payload);
            }
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

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $payload
     */
    private function v2(array $row, array $payload): PublicMediaDecision
    {
        if (! is_array($payload['sourceRevision'] ?? null) || ! is_array($payload['items'] ?? null)) {
            throw new RuntimeException('Invalid Public Media V2 payload.');
        }
        $source = $payload['sourceRevision'];
        $listing = $source['listing'] ?? null;
        $collection = $source['collection'] ?? null;
        if (! is_array($listing) || ! is_array($collection) || ! is_array($source['attachments'] ?? null) || ! is_array($source['assets'] ?? null)) {
            throw new RuntimeException('Invalid Public Media V2 source revision.');
        }
        $revisionSource = new PublicMediaSourceRevisionV2(
            (string) ($listing['listingId'] ?? ''),
            (int) ($listing['publicationVersion'] ?? 0),
            (string) ($listing['state'] ?? ''),
            (string) ($collection['mediaCollectionId'] ?? ''),
            (int) ($collection['collectionVersion'] ?? -1),
            $source['attachments'],
            $source['assets'],
        );
        if (! is_string($payload['sourceRevisionChecksum'] ?? null) || ! hash_equals($revisionSource->checksum(), $payload['sourceRevisionChecksum'])) {
            throw new RuntimeException('Invalid Public Media V2 source checksum.');
        }
        $items = [];
        foreach ($payload['items'] as $item) {
            if (! is_array($item)) {
                throw new RuntimeException('Invalid Public Media V2 item.');
            }
            $items[] = new PublicMediaItemV2(
                (string) ($item['mediaId'] ?? ''),
                (string) ($item['publicLocator'] ?? ''),
                (int) ($item['deliveryRevision'] ?? 0),
                (int) ($item['order'] ?? 0),
                (bool) ($item['primary'] ?? false),
            );
        }
        $origin = rtrim($this->publicOrigin ?? (string) config('app.url'), '/');
        if (filter_var($origin, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('Public Media V2 HTTP origin is unavailable.');
        }
        $gallery = array_map(static fn (PublicMediaItemV2 $item): PublicMediaItem => new PublicMediaItem($item->mediaId, $origin.$item->publicLocator, []), $items);
        $primary = array_values(array_filter($items, static fn (PublicMediaItemV2 $item): bool => $item->primary))[0] ?? null;
        $cover = $primary instanceof PublicMediaItemV2 ? new PublicMediaItem($primary->mediaId, $origin.$primary->publicLocator, []) : null;
        $revision = new PublicMediaRevision(
            PublicMediaRevisionVersion::fromInt((int) $row['version']),
            PublicMediaRevisionChecksum::fromString((string) $row['revision_checksum']),
            (string) $row['causation_key'],
        );
        $decision = new PublicMediaDecision((string) $row['media_collection_id'], $revision, $cover, $gallery, 2, $items, $revisionSource);
        $parameters = $this->parameters($decision);
        if (! hash_equals((string) $row['payload_checksum'], $parameters['payload_checksum'])) {
            throw new RuntimeException('Corrupt Public Media V2 payload checksum.');
        }

        return $decision;
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
