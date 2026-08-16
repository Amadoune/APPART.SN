<?php

namespace App\Application\PublicGeographySource;

use App\Application\PublicGeographyRevision\PublicGeographyRevision;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionChecksum;
use InvalidArgumentException;

final readonly class PublicGeographyDecisionV2
{
    public const SCHEMA_VERSION = 'public-geography-place-representation-v2';

    /**
     * @param  list<PublicGeographyBreadcrumbItemV2>  $breadcrumb
     * @param  list<PublicGeographyRevisionVectorItemV2>  $revisionVector
     */
    public function __construct(
        public string $terminalPlaceId,
        public PublicGeographyDecisionStatusV2 $status,
        public ?string $locality,
        public array $breadcrumb,
        public array $revisionVector,
        public PublicGeographyRevision $revision,
    ) {
        if (trim($terminalPlaceId) === '' || $revisionVector === []) {
            throw new InvalidArgumentException('A public Geography V2 decision requires a terminal identity and revision vector.');
        }
        if ($status === PublicGeographyDecisionStatusV2::Available && (trim((string) $locality) === '' || $breadcrumb === [])) {
            throw new InvalidArgumentException('An available public Geography V2 decision requires locality and breadcrumb.');
        }
        if ($status === PublicGeographyDecisionStatusV2::Unavailable && ($locality !== null || $breadcrumb !== [])) {
            throw new InvalidArgumentException('An unavailable public Geography V2 decision cannot expose public place content.');
        }
        if (! $revision->checksum->equals(PublicGeographyRevisionChecksum::fromCanonicalPayload($this->canonicalPayload()))) {
            throw new InvalidArgumentException('Public Geography V2 revision does not certify its content.');
        }
    }

    public function canonicalPayload(): string
    {
        return json_encode([
            'schemaVersion' => self::SCHEMA_VERSION,
            'terminalPlaceId' => $this->terminalPlaceId,
            'status' => $this->status->value,
            'locality' => $this->locality,
            'breadcrumb' => array_map(static fn (PublicGeographyBreadcrumbItemV2 $item): array => [
                'placeId' => $item->placeId,
                'type' => $item->type,
                'officialName' => $item->officialName,
                'parentPlaceId' => $item->parentPlaceId,
                'aggregateVersion' => $item->aggregateVersion,
            ], $this->breadcrumb),
            'revisionVector' => array_map(static fn (PublicGeographyRevisionVectorItemV2 $item): array => [
                'placeId' => $item->placeId,
                'aggregateVersion' => $item->aggregateVersion,
            ], $this->revisionVector),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
