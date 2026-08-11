<?php

namespace Appart\Modules\Media\Application\ReadyAsset;

use InvalidArgumentException;

final readonly class MediaAssetReadinessRequest
{
    public function __construct(
        public string $assetId,
        public string $intentId,
    ) {
        foreach (['assetId' => $assetId, 'intentId' => $intentId] as $field => $value) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower($value)) !== 1) {
                throw new InvalidArgumentException("Invalid {$field}.");
            }
        }
    }
}
