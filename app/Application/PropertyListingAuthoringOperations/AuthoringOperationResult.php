<?php

namespace App\Application\PropertyListingAuthoringOperations;

final readonly class AuthoringOperationResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public AuthoringOperationStatus $status,
        public array $data = [],
    ) {}
}
