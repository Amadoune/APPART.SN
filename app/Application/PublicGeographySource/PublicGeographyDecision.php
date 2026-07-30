<?php

namespace App\Application\PublicGeographySource;

use App\Application\PublicGeographyRevision\PublicGeographyRevision;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionChecksum;
use InvalidArgumentException;

final readonly class PublicGeographyDecision
{
    /** @param list<PublicGeographyBreadcrumbItem> $breadcrumb */
    public function __construct(public string $placeId, public PublicGeographyRevision $revision, public string $locality, public array $breadcrumb)
    {
        if (trim($placeId) === '' || trim($locality) === '' || $breadcrumb === []) {
            throw new InvalidArgumentException('A public Geography decision requires place, locality and breadcrumb.');
        }
        if (! $revision->checksum->equals(PublicGeographyRevisionChecksum::fromCanonicalPayload($this->canonicalPayload()))) {
            throw new InvalidArgumentException('Public Geography revision does not certify its content.');
        }
    }

    public function canonicalPayload(): string
    {
        $payload = json_encode(['locality' => $this->locality, 'breadcrumb' => array_map(static fn (PublicGeographyBreadcrumbItem $item): array => ['label' => $item->label, 'url' => $item->url], $this->breadcrumb)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return $payload;
    }
}
