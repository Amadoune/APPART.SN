<?php

namespace App\Application\MultiTargetDelivery;

use InvalidArgumentException;

final readonly class MultiTargetPropagationRequest
{
    public function __construct(
        public MultiTargetPropagationSource $source,
        public string $sourceId,
        public string $propertyId,
    ) {
        if (trim($sourceId) === '' || trim($propertyId) === '') {
            throw new InvalidArgumentException('Propagation source and normalized Property identities are required.');
        }
        if ($source === MultiTargetPropagationSource::Property && $sourceId !== $propertyId) {
            throw new InvalidArgumentException('A Property propagation must normalize to its own identity.');
        }
    }
}
