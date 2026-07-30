<?php

namespace App\Application\PropertyListingAuthoringHttp;

final readonly class PropertyListingAuthoringHttpResult
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public PropertyListingAuthoringHttpStatus $status,
        public array $data = [],
    ) {}
}
