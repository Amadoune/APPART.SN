<?php

namespace App\Infrastructure\PublicGeographySource\PostgreSql;

use App\Application\PublicGeographyRevision\PublicGeographyRevision;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionChecksum;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionVersion;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItemV2;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyDecisionStatusV2;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyRevisionVectorItemV2;
use RuntimeException;

final readonly class PostgreSqlPublicGeographyMapper
{
    /** @return array<string,int|string> */
    public function parameters(PublicGeographyDecision|PublicGeographyDecisionV2 $decision): array
    {
        $payload = $decision->canonicalPayload();

        return ['place_id' => $decision instanceof PublicGeographyDecisionV2 ? $decision->terminalPlaceId : $decision->placeId, 'version' => $decision->revision->version->value, 'causation' => $decision->revision->causationKey, 'revision_checksum' => $decision->revision->checksum->value, 'payload' => $payload, 'payload_checksum' => hash('sha256', $payload)];
    }

    /** @param array<string,mixed> $row */
    public function toDecision(array $row): PublicGeographyDecision|PublicGeographyDecisionV2
    {
        try {
            $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)) {
                throw new RuntimeException('Invalid public Geography payload.');
            }
            $schemaVersion = $payload['schemaVersion'] ?? null;
            if ($schemaVersion !== null && $schemaVersion !== PublicGeographyDecisionV2::SCHEMA_VERSION) {
                throw new RuntimeException('Unsupported public Geography schema version.');
            }
            if ($schemaVersion === PublicGeographyDecisionV2::SCHEMA_VERSION) {
                return $this->toDecisionV2($row, $payload);
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

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $payload
     */
    private function toDecisionV2(array $row, array $payload): PublicGeographyDecisionV2
    {
        $breadcrumb = [];
        foreach ((array) ($payload['breadcrumb'] ?? []) as $item) {
            if (! is_array($item) || array_key_exists('url', $item) || array_key_exists('slug', $item)) {
                throw new RuntimeException('Invalid public Geography V2 breadcrumb.');
            }
            $breadcrumb[] = new PublicGeographyBreadcrumbItemV2(
                (string) ($item['placeId'] ?? ''),
                (string) ($item['type'] ?? ''),
                (string) ($item['officialName'] ?? ''),
                isset($item['parentPlaceId']) ? (string) $item['parentPlaceId'] : null,
                (int) ($item['aggregateVersion'] ?? 0),
            );
        }
        $revisionVector = [];
        foreach ((array) ($payload['revisionVector'] ?? []) as $item) {
            if (! is_array($item)) {
                throw new RuntimeException('Invalid public Geography V2 revision vector.');
            }
            $revisionVector[] = new PublicGeographyRevisionVectorItemV2((string) ($item['placeId'] ?? ''), (int) ($item['aggregateVersion'] ?? 0));
        }
        $revision = new PublicGeographyRevision(PublicGeographyRevisionVersion::fromInt((int) $row['version']), PublicGeographyRevisionChecksum::fromString((string) $row['revision_checksum']), (string) $row['causation_key']);
        $decision = new PublicGeographyDecisionV2(
            (string) ($payload['terminalPlaceId'] ?? ''),
            PublicGeographyDecisionStatusV2::from((string) ($payload['status'] ?? '')),
            isset($payload['locality']) ? (string) $payload['locality'] : null,
            $breadcrumb,
            $revisionVector,
            $revision,
        );
        if ($decision->terminalPlaceId !== (string) $row['place_id'] || ! hash_equals((string) $row['payload_checksum'], hash('sha256', $decision->canonicalPayload()))) {
            throw new RuntimeException('Corrupt public Geography V2 identity or checksum.');
        }

        return $decision;
    }
}
