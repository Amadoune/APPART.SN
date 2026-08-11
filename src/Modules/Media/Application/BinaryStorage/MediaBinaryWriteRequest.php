<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

use InvalidArgumentException;

final readonly class MediaBinaryWriteRequest
{
    public function __construct(
        public string $ownerId,
        public string $assetId,
        public string $intentId,
        public string $originalName,
        public string $contentType,
    ) {
        foreach (['ownerId' => $ownerId, 'assetId' => $assetId, 'intentId' => $intentId] as $field => $value) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower($value)) !== 1) {
                throw new InvalidArgumentException("Invalid {$field}.");
            }
        }
        if ($originalName === '' || strlen($originalName) > 255 || basename($originalName) !== $originalName) {
            throw new InvalidArgumentException('Invalid originalName.');
        }
        if (preg_match('#^image/[a-z0-9.+-]{2,64}$#', $contentType) !== 1) {
            throw new InvalidArgumentException('Invalid contentType.');
        }
    }
}
