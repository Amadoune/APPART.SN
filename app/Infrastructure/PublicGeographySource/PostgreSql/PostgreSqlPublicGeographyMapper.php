<?php

namespace App\Infrastructure\PublicGeographySource\PostgreSql;

use App\Application\PublicGeographyRevision\PublicGeographyRevision;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionChecksum;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionVersion;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use RuntimeException;

final readonly class PostgreSqlPublicGeographyMapper
{
    /** @return array<string,int|string> */
    public function parameters(PublicGeographyDecision $decision): array
    {
        $payload = $decision->canonicalPayload();

        return ['place_id' => $decision->placeId, 'version' => $decision->revision->version->value, 'causation' => $decision->revision->causationKey, 'revision_checksum' => $decision->revision->checksum->value, 'payload' => $payload, 'payload_checksum' => hash('sha256', $payload)];
    }

    /** @param array<string,mixed> $row */
    public function toDecision(array $row): PublicGeographyDecision
    {
        try {
            $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)) {
                throw new RuntimeException('Invalid public Geography payload.');
            }
            $breadcrumb = [];
            foreach ((array) ($payload['breadcrumb'] ?? []) as $item) {
                if (! is_array($item)) {
                    throw new RuntimeException('Invalid breadcrumb.');
                }$breadcrumb[] = new PublicGeographyBreadcrumbItem((string) ($item['label'] ?? ''), (string) ($item['url'] ?? ''));
            }
            $revision = new PublicGeographyRevision(PublicGeographyRevisionVersion::fromInt((int) $row['version']), PublicGeographyRevisionChecksum::fromString((string) $row['revision_checksum']), (string) $row['causation_key']);
            $decision = new PublicGeographyDecision((string) $row['place_id'], $revision, (string) ($payload['locality'] ?? ''), $breadcrumb);
            $parameters = $this->parameters($decision);
            if (! hash_equals((string) $row['payload_checksum'], $parameters['payload_checksum'])) {
                throw new RuntimeException('Corrupt public Geography checksum.');
            }

            return $decision;
        } catch (\Throwable $error) {
            throw new RuntimeException('Corrupt public Geography decision.', 0, $error);
        }
    }
}
