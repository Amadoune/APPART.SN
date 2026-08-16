<?php

namespace App\Application\PublicGeographyMaterialization;

use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItemV2;
use App\Application\PublicGeographySource\PublicGeographyDecisionStatusV2;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyRevisionVectorItemV2;
use OverflowException;

final readonly class PublicGeographyDecisionV2Assembler
{
    public function __construct(private PublicGeographyRevisionStrategy $revisions) {}

    public function assemble(PublicGeographyHierarchy $hierarchy, string $causationKey): PublicGeographyDecisionV2
    {
        $terminal = $hierarchy->places[array_key_last($hierarchy->places)];
        $available = true;
        $watermark = 0;
        $breadcrumb = [];
        $vector = [];
        foreach ($hierarchy->places as $place) {
            $version = $place->version();
            if ($version < 1 || $watermark > PHP_INT_MAX - $version) {
                throw new OverflowException('Public Geography revision watermark is invalid.');
            }
            $watermark += $version;
            $available = $available && $place->isEnabled() && $place->mergedInto() === null;
            $breadcrumb[] = new PublicGeographyBreadcrumbItemV2(
                $place->id()->value,
                $place->type()->value,
                $place->officialName()->value,
                $place->parent()?->placeId->value,
                $version,
            );
            $vector[] = new PublicGeographyRevisionVectorItemV2($place->id()->value, $version);
        }

        $status = $available ? PublicGeographyDecisionStatusV2::Available : PublicGeographyDecisionStatusV2::Unavailable;
        $locality = $available ? $terminal->officialName()->value : null;
        $publicBreadcrumb = $available ? $breadcrumb : [];
        $payload = json_encode([
            'schemaVersion' => PublicGeographyDecisionV2::SCHEMA_VERSION,
            'terminalPlaceId' => $terminal->id()->value,
            'status' => $status->value,
            'locality' => $locality,
            'breadcrumb' => array_map(static fn (PublicGeographyBreadcrumbItemV2 $item): array => [
                'placeId' => $item->placeId,
                'type' => $item->type,
                'officialName' => $item->officialName,
                'parentPlaceId' => $item->parentPlaceId,
                'aggregateVersion' => $item->aggregateVersion,
            ], $publicBreadcrumb),
            'revisionVector' => array_map(static fn (PublicGeographyRevisionVectorItemV2 $item): array => [
                'placeId' => $item->placeId,
                'aggregateVersion' => $item->aggregateVersion,
            ], $vector),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new PublicGeographyDecisionV2($terminal->id()->value, $status, $locality, $publicBreadcrumb, $vector, $this->revisions->revise($watermark, $payload, $causationKey));
    }
}
